---
name: csharp-expert
description: Expert C# development agent with deep knowledge of .NET ecosystem, modern C# features, design patterns, and best practices. Use this skill when the user wants to (1) Write, review, or refactor C# code, (2) Design C# applications or architecture, (3) Debug C# issues or optimize performance, (4) Learn C# concepts, patterns, or .NET features, (5) Get guidance on ASP.NET Core, Entity Framework, LINQ, async/await, or other .NET technologies, (6) Create complete C# projects or solutions, (7) Implement design patterns or SOLID principles in C#, (8) Get help with unit testing, dependency injection, or configuration in .NET.
---

# C# Expert Agent

Expert guidance for C# development with focus on modern practices, clean code, and the .NET ecosystem.

## Core Capabilities

### 1. Modern C# Development
- Latest C# features (C# 8.0-13): nullable reference types, records, pattern matching, init-only properties, file-scoped namespaces
- Advanced language features: async/await, LINQ, expression trees, delegates, events, generics
- Functional programming patterns: immutability, pure functions, higher-order functions
- Performance optimization: Span<T>, Memory<T>, ArrayPool, object pooling, ref structs

### 2. .NET Ecosystem Expertise
- **ASP.NET Core**: Web APIs, MVC, Blazor, Razor Pages, minimal APIs, middleware
- **Entity Framework Core**: migrations, LINQ queries, relationships, performance tuning, DbContext patterns
- **Dependency Injection**: service lifetimes (transient, scoped, singleton), factory patterns, service registration
- **Configuration**: appsettings.json, environment variables, user secrets, options pattern
- **Testing**: xUnit, NUnit, MSTest, Moq, FluentAssertions, integration testing
- **Logging**: ILogger, Serilog, structured logging, log levels

### 3. Architecture & Design
- SOLID principles in practice
- Design patterns: Repository, Unit of Work, Factory, Strategy, Observer, Decorator, etc. (see references/design-patterns.md)
- Clean Architecture / Onion Architecture / Hexagonal Architecture
- Domain-Driven Design (DDD): entities, value objects, aggregates, repositories
- Microservices patterns: API Gateway, service discovery, circuit breaker
- CQRS and Event Sourcing fundamentals

## Code Quality Standards

### Naming Conventions
```csharp
// PascalCase: classes, methods, properties, public fields, namespaces
public class OrderService { }
public void ProcessPayment() { }
public string CustomerName { get; set; }

// camelCase: private fields (with underscore prefix), parameters, local variables
private readonly ILogger _logger;
private int _retryCount;
public void CreateUser(string userName, int userId) { }

// UPPER_CASE: constants only
public const int MAX_RETRY_ATTEMPTS = 3;
private const string DEFAULT_CULTURE = "en-US";

// Interface naming: I prefix
public interface IUserRepository { }
```

### Best Practices Checklist

**General Coding:**
- Use nullable reference types (`#nullable enable`) for null safety
- Prefer composition over inheritance
- Follow single responsibility principle (one class, one reason to change)
- Keep methods small and focused (< 20 lines when possible)
- Use meaningful names (avoid abbreviations like `usr`, `ord`)
- Avoid magic numbers (use named constants)
- Use `var` when type is obvious from right side

**Async Programming:**
- Always use `async`/`await`, never `.Result` or `.Wait()`
- Suffix async methods with `Async` (e.g., `GetUserAsync`)
- Use `ConfigureAwait(false)` in libraries
- Return `Task<T>` not `Task<Task<T>>`
- Use `IAsyncEnumerable<T>` for streaming data

**Error Handling:**
- Use specific exception types, not generic `Exception`
- Don't swallow exceptions (avoid empty catch blocks)
- Log exceptions before rethrowing
- Use Result pattern or custom error types for business logic errors
- Validate inputs early (fail fast)

**Resources & Memory:**
- Dispose `IDisposable` objects properly (using statement)
- Avoid memory leaks (unsubscribe from events)
- Use `async` with streams (don't block on I/O)
- Consider using `Span<T>` for performance-critical code

### Modern C# Patterns

**Records for Data Transfer:**
```csharp
// Immutable DTOs
public record UserDto(int Id, string Name, string Email);

// With validation
public record CreateOrderRequest(
    int UserId, 
    List<OrderItem> Items)
{
    public CreateOrderRequest
    {
        ArgumentNullException.ThrowIfNull(Items);
        if (Items.Count == 0)
            throw new ArgumentException("Order must have items", nameof(Items));
    }
}
```

**Pattern Matching:**
```csharp
// Switch expressions
var discount = customer.Type switch
{
    CustomerType.Premium => 0.20m,
    CustomerType.Regular => 0.10m,
    CustomerType.New => 0.05m,
    _ => 0m
};

// Property patterns
var message = response switch
{
    { IsSuccess: true, Data: not null } => $"Success: {response.Data}",
    { StatusCode: 404 } => "Not found",
    { StatusCode: >= 500 } => "Server error",
    _ => "Unknown error"
};

// Type patterns
if (obj is string { Length: > 0 } str)
{
    Console.WriteLine(str);
}
```

**Null Safety:**
```csharp
// Null-coalescing
var name = user?.Name ?? "Unknown";

// Null-coalescing assignment
_cache ??= new Dictionary<string, object>();

// Null-conditional with collection
var firstItem = collection?.FirstOrDefault();
```

**Using Declarations:**
```csharp
// Automatic disposal at end of scope
using var stream = File.OpenRead(path);
using var reader = new StreamReader(stream);
var content = await reader.ReadToEndAsync();
// stream and reader disposed here
```

## Recommended Project Structure

### Clean Architecture Layout
```
MySolution/
├── src/
│   ├── Domain/                    # Core business logic
│   │   ├── Entities/              # Domain entities
│   │   ├── ValueObjects/          # Value objects
│   │   ├── Enums/                 # Enumerations
│   │   ├── Exceptions/            # Domain exceptions
│   │   └── Interfaces/            # Domain interfaces
│   │
│   ├── Application/               # Use cases & orchestration
│   │   ├── Common/
│   │   │   ├── Interfaces/        # IRepository, IUnitOfWork, etc.
│   │   │   ├── Models/            # DTOs, ViewModels
│   │   │   └── Behaviors/         # Pipeline behaviors
│   │   ├── Features/
│   │   │   ├── Users/
│   │   │   │   ├── Commands/      # Create, Update, Delete
│   │   │   │   └── Queries/       # Get, List
│   │   │   └── Orders/
│   │   └── Validators/            # FluentValidation validators
│   │
│   ├── Infrastructure/            # External concerns
│   │   ├── Persistence/
│   │   │   ├── ApplicationDbContext.cs
│   │   │   ├── Configurations/    # EF configurations
│   │   │   └── Repositories/      # Repository implementations
│   │   ├── Identity/              # Authentication/authorization
│   │   ├── Services/              # External service integrations
│   │   └── Migrations/            # EF migrations
│   │
│   └── WebAPI/                    # Presentation layer
│       ├── Controllers/
│       ├── Middleware/
│       ├── Filters/
│       ├── Program.cs
│       └── appsettings.json
│
└── tests/
    ├── Domain.UnitTests/
    ├── Application.UnitTests/
    └── Application.IntegrationTests/
```

### Simple Project Layout (for smaller apps)
```
MyProject/
├── Controllers/
├── Models/
├── Services/
├── Data/
│   ├── ApplicationDbContext.cs
│   └── Migrations/
├── DTOs/
├── Middleware/
├── Program.cs
└── appsettings.json
```

## Common Development Tasks

### 1. Setting Up New Project

**Create Web API:**
```bash
dotnet new webapi -n MyApi
cd MyApi
dotnet add package Microsoft.EntityFrameworkCore.SqlServer
dotnet add package Microsoft.EntityFrameworkCore.Tools
dotnet add package Serilog.AspNetCore
```

**Enable Nullable Reference Types** (add to .csproj):
```xml
<PropertyGroup>
  <Nullable>enable</Nullable>
  <TreatWarningsAsErrors>true</TreatWarningsAsErrors>
</PropertyGroup>
```

### 2. Dependency Injection Setup

**Program.cs (Minimal API style):**
```csharp
var builder = WebApplication.CreateBuilder(args);

// Add services
builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();

// Database
builder.Services.AddDbContext<ApplicationDbContext>(options =>
    options.UseSqlServer(builder.Configuration.GetConnectionString("DefaultConnection")));

// Application services
builder.Services.AddScoped<IUserRepository, UserRepository>();
builder.Services.AddScoped<IUserService, UserService>();
builder.Services.AddSingleton<ICacheService, RedisCacheService>();
builder.Services.AddTransient<IEmailSender, EmailSender>();

// Logging
builder.Services.AddLogging(logging =>
{
    logging.AddConsole();
    logging.AddDebug();
});

var app = builder.Build();

// Middleware pipeline
if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.UseHttpsRedirection();
app.UseAuthentication();
app.UseAuthorization();
app.MapControllers();

app.Run();
```

### 3. Repository Pattern Implementation

**Interface:**
```csharp
public interface IRepository<T> where T : class
{
    Task<T?> GetByIdAsync(int id, CancellationToken cancellationToken = default);
    Task<IEnumerable<T>> GetAllAsync(CancellationToken cancellationToken = default);
    Task<T> AddAsync(T entity, CancellationToken cancellationToken = default);
    Task UpdateAsync(T entity, CancellationToken cancellationToken = default);
    Task DeleteAsync(int id, CancellationToken cancellationToken = default);
}
```

**Implementation:**
```csharp
public class Repository<T> : IRepository<T> where T : class
{
    private readonly ApplicationDbContext _context;
    private readonly DbSet<T> _dbSet;

    public Repository(ApplicationDbContext context)
    {
        _context = context;
        _dbSet = context.Set<T>();
    }

    public async Task<T?> GetByIdAsync(int id, CancellationToken cancellationToken = default)
    {
        return await _dbSet.FindAsync(new object[] { id }, cancellationToken);
    }

    public async Task<IEnumerable<T>> GetAllAsync(CancellationToken cancellationToken = default)
    {
        return await _dbSet.ToListAsync(cancellationToken);
    }

    public async Task<T> AddAsync(T entity, CancellationToken cancellationToken = default)
    {
        await _dbSet.AddAsync(entity, cancellationToken);
        await _context.SaveChangesAsync(cancellationToken);
        return entity;
    }

    public async Task UpdateAsync(T entity, CancellationToken cancellationToken = default)
    {
        _dbSet.Update(entity);
        await _context.SaveChangesAsync(cancellationToken);
    }

    public async Task DeleteAsync(int id, CancellationToken cancellationToken = default)
    {
        var entity = await GetByIdAsync(id, cancellationToken);
        if (entity != null)
        {
            _dbSet.Remove(entity);
            await _context.SaveChangesAsync(cancellationToken);
        }
    }
}
```

### 4. Result Pattern for Error Handling

**Result Type:**
```csharp
public record Result<T>
{
    public bool IsSuccess { get; init; }
    public T? Value { get; init; }
    public string? Error { get; init; }

    public static Result<T> Success(T value) => new() 
    { 
        IsSuccess = true, 
        Value = value 
    };

    public static Result<T> Failure(string error) => new() 
    { 
        IsSuccess = false, 
        Error = error 
    };

    public TResult Match<TResult>(
        Func<T, TResult> onSuccess,
        Func<string, TResult> onFailure)
    {
        return IsSuccess 
            ? onSuccess(Value!) 
            : onFailure(Error!);
    }
}
```

**Usage in Service:**
```csharp
public async Task<Result<User>> CreateUserAsync(CreateUserDto dto)
{
    if (await _userRepository.ExistsByEmailAsync(dto.Email))
    {
        return Result<User>.Failure("Email already exists");
    }

    var user = new User
    {
        Name = dto.Name,
        Email = dto.Email,
        CreatedAt = DateTime.UtcNow
    };

    await _userRepository.AddAsync(user);
    
    return Result<User>.Success(user);
}
```

**Usage in Controller:**
```csharp
[HttpPost]
public async Task<IActionResult> CreateUser([FromBody] CreateUserDto dto)
{
    var result = await _userService.CreateUserAsync(dto);
    
    return result.Match(
        onSuccess: user => CreatedAtAction(nameof(GetUser), new { id = user.Id }, user),
        onFailure: error => BadRequest(new { error })
    );
}
```

### 5. Global Exception Handling

**Custom Middleware:**
```csharp
public class ExceptionHandlingMiddleware
{
    private readonly RequestDelegate _next;
    private readonly ILogger<ExceptionHandlingMiddleware> _logger;

    public ExceptionHandlingMiddleware(
        RequestDelegate next,
        ILogger<ExceptionHandlingMiddleware> logger)
    {
        _next = next;
        _logger = logger;
    }

    public async Task InvokeAsync(HttpContext context)
    {
        try
        {
            await _next(context);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "An unhandled exception occurred");
            await HandleExceptionAsync(context, ex);
        }
    }

    private static async Task HandleExceptionAsync(HttpContext context, Exception exception)
    {
        context.Response.ContentType = "application/json";
        
        var (statusCode, message) = exception switch
        {
            NotFoundException => (StatusCodes.Status404NotFound, exception.Message),
            ValidationException => (StatusCodes.Status400BadRequest, exception.Message),
            UnauthorizedAccessException => (StatusCodes.Status401Unauthorized, "Unauthorized"),
            _ => (StatusCodes.Status500InternalServerError, "An error occurred")
        };

        context.Response.StatusCode = statusCode;

        await context.Response.WriteAsJsonAsync(new
        {
            error = message,
            statusCode
        });
    }
}

// Register in Program.cs
app.UseMiddleware<ExceptionHandlingMiddleware>();
```

### 6. Entity Framework Best Practices

**DbContext Configuration:**
```csharp
public class ApplicationDbContext : DbContext
{
    public ApplicationDbContext(DbContextOptions<ApplicationDbContext> options)
        : base(options)
    {
    }

    public DbSet<User> Users => Set<User>();
    public DbSet<Order> Orders => Set<Order>();

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);
        
        // Apply all configurations from assembly
        modelBuilder.ApplyConfigurationsFromAssembly(typeof(ApplicationDbContext).Assembly);
    }
}
```

**Entity Configuration:**
```csharp
public class UserConfiguration : IEntityTypeConfiguration<User>
{
    public void Configure(EntityTypeBuilder<User> builder)
    {
        builder.ToTable("Users");
        
        builder.HasKey(u => u.Id);
        
        builder.Property(u => u.Name)
            .IsRequired()
            .HasMaxLength(100);
            
        builder.Property(u => u.Email)
            .IsRequired()
            .HasMaxLength(255);
            
        builder.HasIndex(u => u.Email)
            .IsUnique();
            
        builder.HasMany(u => u.Orders)
            .WithOne(o => o.User)
            .HasForeignKey(o => o.UserId)
            .OnDelete(DeleteBehavior.Cascade);
    }
}
```

**Efficient Queries:**
```csharp
// Good: Use AsNoTracking for read-only queries
var users = await _context.Users
    .AsNoTracking()
    .Where(u => u.IsActive)
    .ToListAsync();

// Good: Include related entities to avoid N+1
var orders = await _context.Orders
    .Include(o => o.User)
    .Include(o => o.OrderItems)
        .ThenInclude(oi => oi.Product)
    .ToListAsync();

// Good: Project to DTO to fetch only needed columns
var userDtos = await _context.Users
    .Select(u => new UserDto
    {
        Id = u.Id,
        Name = u.Name,
        Email = u.Email
    })
    .ToListAsync();

// Good: Use pagination
var pagedUsers = await _context.Users
    .OrderBy(u => u.Name)
    .Skip((pageNumber - 1) * pageSize)
    .Take(pageSize)
    .ToListAsync();
```

## Testing Guidelines

### Unit Test Structure
```csharp
[Fact]
public async Task CreateUser_ValidInput_ReturnsSuccess()
{
    // Arrange
    var userRepository = new Mock<IUserRepository>();
    var userService = new UserService(userRepository.Object);
    var createDto = new CreateUserDto("John Doe", "john@example.com");
    
    userRepository.Setup(r => r.ExistsByEmailAsync(createDto.Email))
        .ReturnsAsync(false);
    
    // Act
    var result = await userService.CreateUserAsync(createDto);
    
    // Assert
    result.IsSuccess.Should().BeTrue();
    result.Value.Should().NotBeNull();
    result.Value!.Name.Should().Be("John Doe");
    
    userRepository.Verify(r => r.AddAsync(It.IsAny<User>()), Times.Once);
}

[Fact]
public async Task CreateUser_DuplicateEmail_ReturnsFailure()
{
    // Arrange
    var userRepository = new Mock<IUserRepository>();
    var userService = new UserService(userRepository.Object);
    var createDto = new CreateUserDto("John Doe", "existing@example.com");
    
    userRepository.Setup(r => r.ExistsByEmailAsync(createDto.Email))
        .ReturnsAsync(true);
    
    // Act
    var result = await userService.CreateUserAsync(createDto);
    
    // Assert
    result.IsSuccess.Should().BeFalse();
    result.Error.Should().Be("Email already exists");
    
    userRepository.Verify(r => r.AddAsync(It.IsAny<User>()), Times.Never);
}
```

## Performance Tips

### 1. Avoid Boxing
```csharp
// Bad: Boxing value type
object obj = 42; // int boxed to object

// Good: Use generics
T GetValue<T>() where T : struct { }
```

### 2. Use Span<T> for Slicing
```csharp
// Bad: Creates substring allocation
string part = text.Substring(0, 10);

// Good: No allocation with Span
ReadOnlySpan<char> span = text.AsSpan(0, 10);
```

### 3. StringBuilder for Concatenation
```csharp
// Bad: Multiple string allocations
var result = "";
foreach (var item in items)
{
    result += item + ", ";
}

// Good: Single allocation
var sb = new StringBuilder();
foreach (var item in items)
{
    sb.Append(item).Append(", ");
}
var result = sb.ToString();
```

### 4. Use ArrayPool for Temporary Buffers
```csharp
var pool = ArrayPool<byte>.Shared;
var buffer = pool.Rent(1024);
try
{
    // Use buffer
}
finally
{
    pool.Return(buffer);
}
```

## Additional Resources

For more detailed information:
- **Design Patterns**: See `references/design-patterns.md` for comprehensive C# design pattern implementations
- **Common Snippets**: See `references/common-snippets.md` for frequently used code patterns and utilities
- **Best Practices**: See `references/best-practices.md` for comprehensive coding standards and guidelines

## Response Approach

When helping with C# development:

1. **Understand the context**: Ask clarifying questions about requirements, existing architecture, and constraints
2. **Choose appropriate patterns**: Select designs that fit the problem scale and complexity
3. **Write production-ready code**: Include error handling, logging, validation, and comments
4. **Follow SOLID principles**: Ensure code is maintainable, testable, and extensible
5. **Consider performance**: Balance readability with efficiency; optimize when needed
6. **Use modern C# features**: Leverage records, pattern matching, nullable types, etc.
7. **Include tests**: Provide unit test examples for critical functionality
8. **Explain trade-offs**: Discuss alternative approaches and why one was chosen
9. **Think about deployment**: Consider configuration, logging, monitoring
10. **Code reviews**: Point out potential issues, suggest improvements, explain rationale