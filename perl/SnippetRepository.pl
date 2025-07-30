#!/usr/bin/perl
use strict;
use warnings;
use DBI;
use CGI;
use JSON;
use Digest::SHA qw(sha256_hex);
use File::Path qw(make_path);
use File::Copy;
use Data::Dumper;

# Configuration
my $config = {
    db_host => 'localhost',
    db_name => 'snippet_repo',
    db_user => 'root',
    db_pass => '',
    upload_dir => './uploads',
    max_file_size => 1024 * 1024, # 1MB
    allowed_extensions => [qw(pl pm py js html css php rb java c cpp h hpp sql xml json yaml)]
};

# Initialize database
init_database();

# Handle CGI requests
my $cgi = CGI->new;
print $cgi->header('text/html; charset=utf-8');

my $action = $cgi->param('action') || 'list';

given ($action) {
    when ('list') { list_snippets(); }
    when ('view') { view_snippet(); }
    when ('add') { add_snippet_form(); }
    when ('save') { save_snippet(); }
    when ('edit') { edit_snippet_form(); }
    when ('update') { update_snippet(); }
    when ('delete') { delete_snippet(); }
    when ('search') { search_snippets(); }
    when ('api/list') { api_list_snippets(); }
    when ('api/view') { api_view_snippet(); }
    when ('api/add') { api_add_snippet(); }
    when ('api/update') { api_update_snippet(); }
    when ('api/delete') { api_delete_snippet(); }
    default { list_snippets(); }
}

sub init_database {
    my $dsn = "DBI:mysql:database=$config->{db_name};host=$config->{db_host}";
    my $dbh = DBI->connect($dsn, $config->{db_user}, $config->{db_pass}, {
        RaiseError => 1,
        PrintError => 0,
        mysql_enable_utf8 => 1
    }) or die "Cannot connect to database: " . DBI->errstr;

    # Create tables if they don't exist
    $dbh->do(qq{
        CREATE TABLE IF NOT EXISTS snippets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            code TEXT NOT NULL,
            language VARCHAR(50) NOT NULL,
            tags VARCHAR(500),
            author VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            views INT DEFAULT 0,
            downloads INT DEFAULT 0,
            rating DECIMAL(3,2) DEFAULT 0.00,
            file_path VARCHAR(500),
            file_size INT,
            checksum VARCHAR(64)
        )
    });

    $dbh->do(qq{
        CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            snippet_id INT NOT NULL,
            author VARCHAR(100) NOT NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (snippet_id) REFERENCES snippets(id) ON DELETE CASCADE
        )
    });

    $dbh->do(qq{
        CREATE TABLE IF NOT EXISTS ratings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            snippet_id INT NOT NULL,
            user_ip VARCHAR(45) NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (snippet_id) REFERENCES snippets(id) ON DELETE CASCADE,
            UNIQUE KEY unique_rating (snippet_id, user_ip)
        )
    });

    # Create upload directory
    make_path($config->{upload_dir}) unless -d $config->{upload_dir};

    $dbh->disconnect;
}

sub get_dbh {
    my $dsn = "DBI:mysql:database=$config->{db_name};host=$config->{db_host}";
    return DBI->connect($dsn, $config->{db_user}, $config->{db_pass}, {
        RaiseError => 1,
        PrintError => 0,
        mysql_enable_utf8 => 1
    });
}

sub list_snippets {
    my $cgi = CGI->new;
    my $dbh = get_dbh();
    
    my $page = $cgi->param('page') || 1;
    my $per_page = 20;
    my $offset = ($page - 1) * $per_page;
    
    my $language_filter = $cgi->param('language') || '';
    my $search = $cgi->param('search') || '';
    
    my $where_clause = "WHERE 1=1";
    my @params;
    
    if ($language_filter) {
        $where_clause .= " AND language = ?";
        push @params, $language_filter;
    }
    
    if ($search) {
        $where_clause .= " AND (title LIKE ? OR description LIKE ? OR tags LIKE ?)";
        my $search_term = "%$search%";
        push @params, ($search_term, $search_term, $search_term);
    }
    
    # Get total count
    my $count_sth = $dbh->prepare("SELECT COUNT(*) FROM snippets $where_clause");
    $count_sth->execute(@params);
    my $total_count = $count_sth->fetchrow_array;
    
    # Get snippets
    my $sth = $dbh->prepare(qq{
        SELECT id, title, description, language, tags, author, created_at, views, rating
        FROM snippets $where_clause
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    });
    $sth->execute(@params, $per_page, $offset);
    
    my @snippets;
    while (my $row = $sth->fetchrow_hashref) {
        push @snippets, $row;
    }
    
    # Get languages for filter
    my $lang_sth = $dbh->prepare("SELECT DISTINCT language FROM snippets ORDER BY language");
    $lang_sth->execute;
    my @languages;
    while (my ($lang) = $lang_sth->fetchrow_array) {
        push @languages, $lang;
    }
    
    $dbh->disconnect;
    
    print_html_header("Code Snippet Repository");
    print_navigation();
    
    print qq{
        <div class="container">
            <div class="header">
                <h1>Code Snippet Repository</h1>
                <p>Share and discover useful code snippets</p>
            </div>
            
            <div class="filters">
                <form method="GET" action="" class="search-form">
                    <input type="hidden" name="action" value="list">
                    <input type="text" name="search" value="$search" placeholder="Search snippets..." class="search-input">
                    <select name="language" class="language-select">
                        <option value="">All Languages</option>
    };
    
    foreach my $lang (@languages) {
        my $selected = $language_filter eq $lang ? 'selected' : '';
        print qq{<option value="$lang" $selected>$lang</option>};
    }
    
    print qq{
                    </select>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
            
            <div class="actions">
                <a href="?action=add" class="btn btn-success">Add New Snippet</a>
            </div>
            
            <div class="snippets-grid">
    };
    
    if (@snippets) {
        foreach my $snippet (@snippets) {
            my $rating_stars = generate_stars($snippet->{rating});
            my $tags = $snippet->{tags} ? join(' ', map { "<span class='tag'>$_</span>" } split(/,/, $snippet->{tags})) : '';
            
            print qq{
                <div class="snippet-card">
                    <div class="snippet-header">
                        <h3><a href="?action=view&id=$snippet->{id}">$snippet->{title}</a></h3>
                        <span class="language-badge $snippet->{language}">$snippet->{language}</span>
                    </div>
                    <p class="description">$snippet->{description}</p>
                    <div class="tags">$tags</div>
                    <div class="meta">
                        <span class="author">by $snippet->{author}</span>
                        <span class="views">$snippet->{views} views</span>
                        <span class="rating">$rating_stars</span>
                    </div>
                    <div class="actions">
                        <a href="?action=view&id=$snippet->{id}" class="btn btn-sm btn-primary">View</a>
                        <a href="?action=edit&id=$snippet->{id}" class="btn btn-sm btn-secondary">Edit</a>
                    </div>
                </div>
            };
        }
    } else {
        print qq{<div class="no-results">No snippets found.</div>};
    }
    
    print qq{</div>};
    
    # Pagination
    if ($total_count > $per_page) {
        my $total_pages = int(($total_count - 1) / $per_page) + 1;
        print qq{<div class="pagination">};
        
        if ($page > 1) {
            print qq{<a href="?action=list&page=@{[$page-1]}&language=$language_filter&search=$search" class="btn">Previous</a>};
        }
        
        for (my $i = 1; $i <= $total_pages; $i++) {
            my $active = $i == $page ? 'active' : '';
            print qq{<a href="?action=list&page=$i&language=$language_filter&search=$search" class="btn $active">$i</a>};
        }
        
        if ($page < $total_pages) {
            print qq{<a href="?action=list&page=@{[$page+1]}&language=$language_filter&search=$search" class="btn">Next</a>};
        }
        
        print qq{</div>};
    }
    
    print qq{</div>};
    print_html_footer();
}

sub view_snippet {
    my $cgi = CGI->new;
    my $id = $cgi->param('id') or die "No snippet ID provided";
    
    my $dbh = get_dbh();
    
    # Increment view count
    $dbh->do("UPDATE snippets SET views = views + 1 WHERE id = ?", undef, $id);
    
    # Get snippet
    my $sth = $dbh->prepare(qq{
        SELECT * FROM snippets WHERE id = ?
    });
    $sth->execute($id);
    my $snippet = $sth->fetchrow_hashref;
    
    if (!$snippet) {
        print "Snippet not found.";
        return;
    }
    
    # Get comments
    my $comment_sth = $dbh->prepare(qq{
        SELECT * FROM comments WHERE snippet_id = ? ORDER BY created_at DESC
    });
    $comment_sth->execute($id);
    
    my @comments;
    while (my $comment = $comment_sth->fetchrow_hashref) {
        push @comments, $comment;
    }
    
    $dbh->disconnect;
    
    print_html_header("$snippet->{title} - Code Snippet Repository");
    print_navigation();
    
    my $rating_stars = generate_stars($snippet->{rating});
    my $tags = $snippet->{tags} ? join(' ', map { "<span class='tag'>$_</span>" } split(/,/, $snippet->{tags})) : '';
    
    print qq{
        <div class="container">
            <div class="snippet-detail">
                <div class="snippet-header">
                    <h1>$snippet->{title}</h1>
                    <div class="meta">
                        <span class="language-badge $snippet->{language}">$snippet->{language}</span>
                        <span class="author">by $snippet->{author}</span>
                        <span class="date">$snippet->{created_at}</span>
                        <span class="views">$snippet->{views} views</span>
                        <span class="rating">$rating_stars</span>
                    </div>
                </div>
                
                <div class="description">
                    <p>$snippet->{description}</p>
                </div>
                
                <div class="tags">$tags</div>
                
                <div class="code-block">
                    <div class="code-header">
                        <span>Code</span>
                        <button class="btn btn-sm btn-secondary" onclick="copyToClipboard()">Copy</button>
                    </div>
                    <pre><code class="language-$snippet->{language}">$snippet->{code}</code></pre>
                </div>
                
                <div class="actions">
                    <a href="?action=edit&id=$id" class="btn btn-secondary">Edit</a>
                    <a href="?action=delete&id=$id" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    <a href="download.pl?id=$id" class="btn btn-primary">Download</a>
                </div>
            </div>
            
            <div class="comments-section">
                <h3>Comments</h3>
                <form method="POST" action="?action=add_comment" class="comment-form">
                    <input type="hidden" name="snippet_id" value="$id">
                    <textarea name="comment" placeholder="Add a comment..." required></textarea>
                    <input type="text" name="author" placeholder="Your name" required>
                    <button type="submit" class="btn btn-primary">Add Comment</button>
                </form>
                
                <div class="comments-list">
    };
    
    foreach my $comment (@comments) {
        print qq{
            <div class="comment">
                <div class="comment-header">
                    <span class="author">$comment->{author}</span>
                    <span class="date">$comment->{created_at}</span>
                </div>
                <p>$comment->{comment}</p>
            </div>
        };
    }
    
    print qq{
                </div>
            </div>
        </div>
    };
    
    print_html_footer();
}

sub add_snippet_form {
    print_html_header("Add New Snippet - Code Snippet Repository");
    print_navigation();
    
    print qq{
        <div class="container">
            <h1>Add New Snippet</h1>
            
            <form method="POST" action="?action=save" enctype="multipart/form-data" class="snippet-form">
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="code">Code *</label>
                    <textarea id="code" name="code" required class="form-control" rows="15"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="language">Language *</label>
                    <select id="language" name="language" required class="form-control">
                        <option value="">Select Language</option>
                        <option value="perl">Perl</option>
                        <option value="python">Python</option>
                        <option value="javascript">JavaScript</option>
                        <option value="html">HTML</option>
                        <option value="css">CSS</option>
                        <option value="php">PHP</option>
                        <option value="ruby">Ruby</option>
                        <option value="java">Java</option>
                        <option value="c">C</option>
                        <option value="cpp">C++</option>
                        <option value="sql">SQL</option>
                        <option value="xml">XML</option>
                        <option value="json">JSON</option>
                        <option value="yaml">YAML</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tags">Tags (comma-separated)</label>
                    <input type="text" id="tags" name="tags" class="form-control" placeholder="e.g., web, database, api">
                </div>
                
                <div class="form-group">
                    <label for="author">Author *</label>
                    <input type="text" id="author" name="author" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="file">Upload File (optional)</label>
                    <input type="file" id="file" name="file" class="form-control">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Snippet</button>
                    <a href="?action=list" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    };
    
    print_html_footer();
}

sub save_snippet {
    my $cgi = CGI->new;
    my $dbh = get_dbh();
    
    my $title = $cgi->param('title') or die "Title is required";
    my $description = $cgi->param('description') || '';
    my $code = $cgi->param('code') or die "Code is required";
    my $language = $cgi->param('language') or die "Language is required";
    my $tags = $cgi->param('tags') || '';
    my $author = $cgi->param('author') or die "Author is required";
    
    # Handle file upload
    my $file_path = '';
    my $file_size = 0;
    my $checksum = sha256_hex($code);
    
    my $upload = $cgi->upload('file');
    if ($upload) {
        my $filename = $cgi->param('file');
        my $ext = '';
        if ($filename =~ /\.(\w+)$/) {
            $ext = $1;
        }
        
        # Validate file extension
        my $allowed = 0;
        foreach my $allowed_ext (@{$config->{allowed_extensions}}) {
            if (lc($ext) eq $allowed_ext) {
                $allowed = 1;
                last;
            }
        }
        
        if (!$allowed) {
            die "File type not allowed. Allowed extensions: " . join(', ', @{$config->{allowed_extensions}});
        }
        
        # Generate unique filename
        my $unique_filename = time() . "_" . int(rand(10000)) . ".$ext";
        $file_path = "$config->{upload_dir}/$unique_filename";
        
        # Copy uploaded file
        copy($upload, $file_path) or die "Failed to save uploaded file";
        $file_size = -s $file_path;
    }
    
    # Insert into database
    my $sth = $dbh->prepare(qq{
        INSERT INTO snippets (title, description, code, language, tags, author, file_path, file_size, checksum)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    });
    
    $sth->execute($title, $description, $code, $language, $tags, $author, $file_path, $file_size, $checksum);
    my $id = $dbh->last_insert_id(undef, undef, 'snippets', 'id');
    
    $dbh->disconnect;
    
    # Redirect to view page
    print $cgi->redirect("?action=view&id=$id");
}

sub generate_stars {
    my $rating = shift || 0;
    my $stars = '';
    
    for (my $i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<span class="star filled">★</span>';
        } else {
            $stars .= '<span class="star">☆</span>';
        }
    }
    
    return $stars;
}

sub print_html_header {
    my $title = shift;
    
    print qq{
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>$title</title>
            <link rel="stylesheet" href="styles.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css">
        </head>
        <body>
    };
}

sub print_navigation {
    print qq{
        <nav class="navbar">
            <div class="nav-container">
                <a href="?action=list" class="nav-brand">Code Snippet Repository</a>
                <div class="nav-menu">
                    <a href="?action=list" class="nav-link">Browse</a>
                    <a href="?action=add" class="nav-link">Add Snippet</a>
                    <a href="?action=search" class="nav-link">Search</a>
                </div>
            </div>
        </nav>
    };
}

sub print_html_footer {
    print qq{
            <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-core.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/plugins/autoloader/prism-autoloader.min.js"></script>
            <script>
                function copyToClipboard() {
                    const codeElement = document.querySelector('pre code');
                    const textArea = document.createElement('textarea');
                    textArea.value = codeElement.textContent;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    
                    const button = event.target;
                    const originalText = button.textContent;
                    button.textContent = 'Copied!';
                    setTimeout(() => {
                        button.textContent = originalText;
                    }, 2000);
                }
            </script>
        </body>
        </html>
    };
} 