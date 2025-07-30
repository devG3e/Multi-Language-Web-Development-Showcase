class Admin::DashboardController < Admin::BaseController
  def index
    @total_users = User.count
    @active_users = User.where(active: true).count
    @total_posts = Post.count
    @total_comments = Comment.count
    @recent_activities = Activity.order(created_at: :desc).limit(10)
    @system_stats = SystemStatistic.latest
  end

  def analytics
    @user_growth = User.group_by_month(:created_at, last: 12).count
    @post_activity = Post.group_by_month(:created_at, last: 12).count
    @popular_posts = Post.joins(:views).group(:id).order('COUNT(views.id) DESC').limit(10)
  end

  def system_health
    @cpu_usage = SystemMetric.where(metric_type: 'cpu').last(24)
    @memory_usage = SystemMetric.where(metric_type: 'memory').last(24)
    @disk_usage = SystemMetric.where(metric_type: 'disk').last(24)
  end
end 