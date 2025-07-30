using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using TaskManager.Data;
using TaskManager.DTOs;
using TaskManager.Models;
using TaskManager.Services;

namespace TaskManager.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    [Authorize]
    public class TasksController : ControllerBase
    {
        private readonly ApplicationDbContext _context;
        private readonly ITaskService _taskService;
        private readonly ILogger<TasksController> _logger;

        public TasksController(
            ApplicationDbContext context,
            ITaskService taskService,
            ILogger<TasksController> logger)
        {
            _context = context;
            _taskService = taskService;
            _logger = logger;
        }

        // GET: api/tasks
        [HttpGet]
        public async Task<ActionResult<PaginatedResult<TaskDto>>> GetTasks(
            [FromQuery] TaskFilterDto filter,
            [FromQuery] int page = 1,
            [FromQuery] int pageSize = 20)
        {
            try
            {
                var userId = User.GetUserId();
                var tasks = await _taskService.GetTasksAsync(userId, filter, page, pageSize);
                return Ok(tasks);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error retrieving tasks");
                return StatusCode(500, new { message = "An error occurred while retrieving tasks" });
            }
        }

        // GET: api/tasks/5
        [HttpGet("{id}")]
        public async Task<ActionResult<TaskDto>> GetTask(int id)
        {
            try
            {
                var userId = User.GetUserId();
                var task = await _taskService.GetTaskByIdAsync(id, userId);

                if (task == null)
                {
                    return NotFound(new { message = "Task not found" });
                }

                return Ok(task);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error retrieving task {TaskId}", id);
                return StatusCode(500, new { message = "An error occurred while retrieving the task" });
            }
        }

        // POST: api/tasks
        [HttpPost]
        public async Task<ActionResult<TaskDto>> CreateTask(CreateTaskDto createTaskDto)
        {
            try
            {
                var userId = User.GetUserId();
                var task = await _taskService.CreateTaskAsync(createTaskDto, userId);
                
                return CreatedAtAction(nameof(GetTask), new { id = task.Id }, task);
            }
            catch (ValidationException ex)
            {
                return BadRequest(new { message = ex.Message });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error creating task");
                return StatusCode(500, new { message = "An error occurred while creating the task" });
            }
        }

        // PUT: api/tasks/5
        [HttpPut("{id}")]
        public async Task<IActionResult> UpdateTask(int id, UpdateTaskDto updateTaskDto)
        {
            try
            {
                var userId = User.GetUserId();
                var task = await _taskService.UpdateTaskAsync(id, updateTaskDto, userId);

                if (task == null)
                {
                    return NotFound(new { message = "Task not found" });
                }

                return Ok(task);
            }
            catch (ValidationException ex)
            {
                return BadRequest(new { message = ex.Message });
            }
            catch (UnauthorizedAccessException)
            {
                return Forbid();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error updating task {TaskId}", id);
                return StatusCode(500, new { message = "An error occurred while updating the task" });
            }
        }

        // DELETE: api/tasks/5
        [HttpDelete("{id}")]
        public async Task<IActionResult> DeleteTask(int id)
        {
            try
            {
                var userId = User.GetUserId();
                var result = await _taskService.DeleteTaskAsync(id, userId);

                if (!result)
                {
                    return NotFound(new { message = "Task not found" });
                }

                return NoContent();
            }
            catch (UnauthorizedAccessException)
            {
                return Forbid();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error deleting task {TaskId}", id);
                return StatusCode(500, new { message = "An error occurred while deleting the task" });
            }
        }

        // PATCH: api/tasks/5/status
        [HttpPatch("{id}/status")]
        public async Task<ActionResult<TaskDto>> UpdateTaskStatus(int id, UpdateTaskStatusDto statusDto)
        {
            try
            {
                var userId = User.GetUserId();
                var task = await _taskService.UpdateTaskStatusAsync(id, statusDto.Status, userId);

                if (task == null)
                {
                    return NotFound(new { message = "Task not found" });
                }

                return Ok(task);
            }
            catch (ValidationException ex)
            {
                return BadRequest(new { message = ex.Message });
            }
            catch (UnauthorizedAccessException)
            {
                return Forbid();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error updating task status {TaskId}", id);
                return StatusCode(500, new { message = "An error occurred while updating the task status" });
            }
        }

        // PATCH: api/tasks/5/assign
        [HttpPatch("{id}/assign")]
        public async Task<ActionResult<TaskDto>> AssignTask(int id, AssignTaskDto assignDto)
        {
            try
            {
                var userId = User.GetUserId();
                var task = await _taskService.AssignTaskAsync(id, assignDto.AssignedToId, userId);

                if (task == null)
                {
                    return NotFound(new { message = "Task not found" });
                }

                return Ok(task);
            }
            catch (ValidationException ex)
            {
                return BadRequest(new { message = ex.Message });
            }
            catch (UnauthorizedAccessException)
            {
                return Forbid();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error assigning task {TaskId}", id);
                return StatusCode(500, new { message = "An error occurred while assigning the task" });
            }
        }

        // GET: api/tasks/dashboard
        [HttpGet("dashboard")]
        public async Task<ActionResult<DashboardDto>> GetDashboard()
        {
            try
            {
                var userId = User.GetUserId();
                var dashboard = await _taskService.GetDashboardAsync(userId);
                return Ok(dashboard);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error retrieving dashboard");
                return StatusCode(500, new { message = "An error occurred while retrieving the dashboard" });
            }
        }

        // GET: api/tasks/overdue
        [HttpGet("overdue")]
        public async Task<ActionResult<List<TaskDto>>> GetOverdueTasks()
        {
            try
            {
                var userId = User.GetUserId();
                var tasks = await _taskService.GetOverdueTasksAsync(userId);
                return Ok(tasks);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error retrieving overdue tasks");
                return StatusCode(500, new { message = "An error occurred while retrieving overdue tasks" });
            }
        }
    }
} 