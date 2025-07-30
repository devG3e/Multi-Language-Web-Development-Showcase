using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace TaskManager.Models
{
    public class Task
    {
        [Key]
        public int Id { get; set; }

        [Required]
        [StringLength(200)]
        public string Title { get; set; } = string.Empty;

        [StringLength(1000)]
        public string? Description { get; set; }

        [Required]
        public TaskPriority Priority { get; set; } = TaskPriority.Medium;

        [Required]
        public TaskStatus Status { get; set; } = TaskStatus.Todo;

        public DateTime? DueDate { get; set; }

        public DateTime CreatedAt { get; set; } = DateTime.UtcNow;

        public DateTime? UpdatedAt { get; set; }

        public DateTime? CompletedAt { get; set; }

        [Required]
        public string CreatedById { get; set; } = string.Empty;

        [ForeignKey("CreatedById")]
        public virtual ApplicationUser CreatedBy { get; set; } = null!;

        public string? AssignedToId { get; set; }

        [ForeignKey("AssignedToId")]
        public virtual ApplicationUser? AssignedTo { get; set; }

        public int? ProjectId { get; set; }

        [ForeignKey("ProjectId")]
        public virtual Project? Project { get; set; }

        public virtual ICollection<TaskComment> Comments { get; set; } = new List<TaskComment>();

        public virtual ICollection<TaskAttachment> Attachments { get; set; } = new List<TaskAttachment>();

        public virtual ICollection<TaskHistory> History { get; set; } = new List<TaskHistory>();

        public virtual ICollection<TaskTag> TaskTags { get; set; } = new List<TaskTag>();

        // Computed properties
        [NotMapped]
        public bool IsOverdue => DueDate.HasValue && DueDate.Value < DateTime.UtcNow && Status != TaskStatus.Completed;

        [NotMapped]
        public bool IsCompleted => Status == TaskStatus.Completed;

        [NotMapped]
        public TimeSpan? TimeToDue => DueDate?.Subtract(DateTime.UtcNow);

        [NotMapped]
        public string PriorityColor => Priority switch
        {
            TaskPriority.Low => "#28a745",
            TaskPriority.Medium => "#ffc107",
            TaskPriority.High => "#fd7e14",
            TaskPriority.Critical => "#dc3545",
            _ => "#6c757d"
        };

        [NotMapped]
        public string StatusColor => Status switch
        {
            TaskStatus.Todo => "#6c757d",
            TaskStatus.InProgress => "#007bff",
            TaskStatus.Review => "#17a2b8",
            TaskStatus.Completed => "#28a745",
            TaskStatus.Cancelled => "#dc3545",
            _ => "#6c757d"
        };
    }

    public enum TaskPriority
    {
        Low = 1,
        Medium = 2,
        High = 3,
        Critical = 4
    }

    public enum TaskStatus
    {
        Todo = 1,
        InProgress = 2,
        Review = 3,
        Completed = 4,
        Cancelled = 5
    }
} 