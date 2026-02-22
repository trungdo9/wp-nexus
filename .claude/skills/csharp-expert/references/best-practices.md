# C# Best Practices & Guidelines

Comprehensive guide to writing professional, maintainable C# code.

## Code Organization

### File Structure
- One class per file (unless nested/related types)
- File name matches the main type name
- Use folder structure to reflect namespace hierarchy
- Group related files in folders (Controllers, Services, Models, etc.)

### Namespace Organization
```csharp
// Align namespaces with folder structure
namespace MyApp.Features.Users.Services
{
    public class UserService { }
}

// Use file-scoped namespaces (C# 10+)
namespace MyApp.Features.Orders;

public class OrderService { }
```

### Using Directives
```csharp
// Order: System namespaces first, then third-party, then project
using System;
using System.Collections.Generic;
using System.Linq;
using Microsoft.Extensions.Logging;
using MyApp.Core.Interfaces;
using MyApp.Domain.Entities;

// Use global usings for commonly used namespaces (C# 10+)
// In a GlobalUsings.cs file:
global using System;
global using System.Collections.Generic;
global using System.Linq;
global using Microsoft.Extensions.Logging;
```

## Naming Conventions

### General Rules
- Use meaningful, descriptive names
- Avoid abbreviations (except well-known ones like `Id`, `Url`, `Html`)
- Don't use Hungarian notation
- Prefer clarity over brevity

### Specific Conventions
```csharp
// Classes, Interfaces, Structs, Enums: PascalCase
public class CustomerService { }
public interface IRepository<T> { }
public struct Point { }
public enum OrderStatus { }

// Methods, Properties: PascalCase
public void ProcessOrder() { }
public string FullName { get; set; }

// Private fields: _camelCase with underscore prefix
private readonly ILogger _logger;
private int _retryCount;

// Parameters, Local variables: camelCase
public void CreateUser(string userName, int userId)
{
    var emailAddress = GetEmail(userId);
}

// Constants: UPPER_CASE (or PascalCase for public)
private const int MAX_RETRY_ATTEMPTS = 3;
public const string ApiVersion = "v1";

// Async methods: suffix with "Async"
public async Task<User> GetUserAsync(int id) { }
```

## SOLID Principles in Practice

### Single Responsibility Principle (SRP)
Each class should have only one reason to change.

```csharp
// Bad: UserService doing too much
public class UserService
{
    public void CreateUser() { }
    public void SendWelcomeEmail() { }
    public void LogActivity() { }
    public void ValidateUser() { }
}

// Good: Separate responsibilities
public class UserService
{
    private readonly IUserRepository _repository;
    private readonly IEmailService _emailService;
    private readonly IUserValidator _validator;
    
    public async Task CreateUserAsync(CreateUserDto dto)
    {
        _validator.Validate(dto);
        var user = await _repository.CreateAsync(dto);
        await _emailService.SendWelcomeEmailAsync(user);
        return user;
    }
}
```

### Open/Closed Principle (OCP)
Open for extension, closed for modification.

```csharp
// Bad: Need to modify class to add new discount types
public class DiscountCalculator
{
    public decimal Calculate(string type, decimal amount)
    {
        return type switch
        {
            "Percentage" => amount * 0.1m,
            "Fixed" => 10m,
            _ => 0m
        };
    }
}

// Good: Use polymorphism
public interface IDiscountStrategy
{
    decimal Calculate(decimal amount);
}

public class PercentageDiscount : IDiscountStrategy
{
    private readonly decimal _percentage;
    public PercentageDiscount(decimal percentage) => _percentage = percentage;
    public decimal Calculate(decimal amount) => amount * _percentage;
}

public class FixedDiscount : IDiscountStrategy
{
    private readonly decimal _amount;
    public FixedDiscount(decimal amount) => _amount = amount;
    public decimal Calculate(decimal amount) => _amount;
}
```

### Liskov Substitution Principle (LSP)
Derived classes must be substitutable for their base classes.

```csharp
// Bad: Violates LSP - Square changes behavior
public class Rectangle
{
    public virtual int Width { get; set; }
    public virtual int Height { get; set; }
}

public class Square : Rectangle
{
    public override int Width
    {
        get => base.Width;
        set { base.Width = value; base.Height = value; }
    }
}

// Good: Separate types
public interface IShape
{
    int CalculateArea();
}

public class Rectangle : IShape
{
    public int Width { get; set; }
    public int Height { get; set; }
    public int CalculateArea() => Width * Height;
}

public class Square : IShape
{
    public int Side { get; set; }
    public int CalculateArea() => Side * Side;
}
```

### Interface Segregation Principle (ISP)
Clients should not depend on interfaces they don't use.

```csharp
// Bad: Fat interface
public interface IRepository
{
    void Add();
    void Update();
    void Delete();
    void ExportToPdf();
    void SendEmail();
}

// Good: Segregated interfaces
public interface IRepository
{
    void Add();
    void Update();
    void Delete();
}

public interface IExportable
{
    void ExportToPdf();
}

public interface INotifiable
{
    void SendEmail();
}
```

### Dependency Inversion Principle (DIP)
Depend on abstractions, not concretions.

```csharp
// Bad: Depends on concrete class
public class UserService
{
    private readonly SqlUserRepository _repository;
    
    public UserService()
    {
        _repository = new SqlUserRepository(); // Tight coupling
    }
}

// Good: Depends on abstraction
public class UserService
{
    private readonly IUserRepository _repository;
    
    public UserService(IUserRepository repository)
    {
        _repository = repository; // Dependency injection
    }
}
```

## Error Handling Best Practices

### Use Specific Exceptions
```csharp
// Bad: Generic exception
throw new Exception("User not found");

// Good: Specific exception
public class UserNotFoundException : Exception
{
    public int UserId { get; }
    
    public UserNotFoundException(int userId) 
        : base($"User with ID {userId} not found")
    {
        UserId = userId;
    }
}

throw new UserNotFoundException(userId);
```

### Don't Swallow Exceptions
```csharp
// Bad: Silent failure
try
{
    await ProcessDataAsync();
}
catch { } // Don't do this!

// Good: Log and handle appropriately
try
{
    await ProcessDataAsync();
}
catch (Exception ex)
{
    _logger.LogError(ex, "Failed to process data");
    throw; // Re-throw or handle appropriately
}
```

### Validate Early
```csharp
public async Task<User> CreateUserAsync(CreateUserDto dto)
{
    // Fail fast with validation
    if (dto == null)
        throw new ArgumentNullException(nameof(dto));
    
    if (string.IsNullOrWhiteSpace(dto.Email))
        throw new ArgumentException("Email is required", nameof(dto));
    
    // Continue with business logic
    return await _repository.CreateAsync(dto);
}
```

## Async/Await Guidelines

### Always Use Async/Await (Never Block)
```csharp
// Bad: Blocking async code
public User GetUser(int id)
{
    return _repository.GetUserAsync(id).Result; // Deadlock risk!
}

// Good: Async all the way
public async Task<User> GetUserAsync(int id)
{
    return await _repository.GetUserAsync(id);
}
```

### ConfigureAwait in Libraries
```csharp
// In library code (not UI/API)
public async Task<string> GetDataAsync()
{
    var result = await _httpClient.GetStringAsync(url)
        .ConfigureAwait(false); // Don't capture context
    return result;
}

// In application code (UI/API), ConfigureAwait not needed
public async Task<IActionResult> GetUser(int id)
{
    var user = await _userService.GetUserAsync(id);
    return Ok(user);
}
```

### Async Void Only for Event Handlers
```csharp
// Bad: Async void in regular method
public async void ProcessData() { } // Don't do this!

// Good: Async Task
public async Task ProcessDataAsync() { }

// Exception: Event handlers
private async void Button_Click(object sender, EventArgs e)
{
    await ProcessDataAsync();
}
```

### Use CancellationToken
```csharp
public async Task<List<User>> GetUsersAsync(CancellationToken cancellationToken = default)
{
    return await _context.Users
        .ToListAsync(cancellationToken);
}

// Usage with timeout
using var cts = new CancellationTokenSource(TimeSpan.FromSeconds(30));
var users = await GetUsersAsync(cts.Token);
```

## Memory Management

### Dispose Resources Properly
```csharp
// Bad: Not disposing
var stream = File.OpenRead(path);
// Use stream...
// Stream not closed!

// Good: Using statement
using var stream = File.OpenRead(path);
// Stream automatically disposed

// Also good: Traditional using
using (var stream = File.OpenRead(path))
{
    // Use stream
} // Stream disposed here
```

### Avoid Memory Leaks
```csharp
// Bad: Event subscription without cleanup
public class Publisher
{
    public event EventHandler DataChanged;
}

public class Subscriber
{
    public Subscriber(Publisher publisher)
    {
        publisher.DataChanged += OnDataChanged; // Leak if not unsubscribed
    }
    
    private void OnDataChanged(object sender, EventArgs e) { }
}

// Good: Unsubscribe or use weak references
public class Subscriber : IDisposable
{
    private readonly Publisher _publisher;
    
    public Subscriber(Publisher publisher)
    {
        _publisher = publisher;
        _publisher.DataChanged += OnDataChanged;
    }
    
    public void Dispose()
    {
        _publisher.DataChanged -= OnDataChanged;
    }
    
    private void OnDataChanged(object sender, EventArgs e) { }
}
```

## Performance Considerations

### Use StringBuilder for String Concatenation
```csharp
// Bad: Creates many string objects
string result = "";
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

### LINQ Deferred Execution
```csharp
// Understand when queries execute
var query = users.Where(u => u.IsActive); // Not executed yet
var activeUsers = query.ToList(); // Executed here

// Avoid multiple enumeration
var expensiveQuery = GetComplexQuery();
var count = expensiveQuery.Count(); // Executes
var first = expensiveQuery.First(); // Executes again!

// Better: materialize once
var results = expensiveQuery.ToList();
var count = results.Count;
var first = results.First();
```

### Use Appropriate Collection Types
```csharp
// List<T>: Ordered, indexed access, frequent additions
var users = new List<User>();

// HashSet<T>: Unique items, fast lookups
var uniqueIds = new HashSet<int>();

// Dictionary<K,V>: Key-value pairs, fast lookups
var userCache = new Dictionary<int, User>();

// ImmutableList<T>: Thread-safe, no modifications
var config = ImmutableList.Create(1, 2, 3);
```

## Testing Best Practices

### Arrange-Act-Assert Pattern
```csharp
[Fact]
public async Task CreateUser_ValidInput_ReturnsUser()
{
    // Arrange
    var repository = new Mock<IUserRepository>();
    var service = new UserService(repository.Object);
    var dto = new CreateUserDto { Name = "John", Email = "john@test.com" };
    
    // Act
    var result = await service.CreateUserAsync(dto);
    
    // Assert
    Assert.NotNull(result);
    Assert.Equal("John", result.Name);
    repository.Verify(r => r.CreateAsync(It.IsAny<User>()), Times.Once);
}
```

### Test One Thing at a Time
```csharp
// Bad: Testing multiple things
[Fact]
public void TestEverything()
{
    var user = CreateUser();
    Assert.NotNull(user);
    Assert.True(user.IsValid);
    Assert.Equal("test@test.com", user.Email);
    // Too many assertions
}

// Good: Focused tests
[Fact]
public void CreateUser_ShouldNotBeNull()
{
    var user = CreateUser();
    Assert.NotNull(user);
}

[Fact]
public void CreateUser_ShouldBeValid()
{
    var user = CreateUser();
    Assert.True(user.IsValid);
}
```

### Use Descriptive Test Names
```csharp
// Bad
[Fact]
public void Test1() { }

// Good: Method_Scenario_ExpectedBehavior
[Fact]
public void CreateUser_WithValidEmail_ReturnsUser() { }

[Fact]
public void CreateUser_WithDuplicateEmail_ThrowsException() { }
```

## Security Best Practices

### Validate User Input
```csharp
public async Task<User> UpdateUserAsync(int id, UpdateUserDto dto)
{
    // Validate input
    if (id <= 0)
        throw new ArgumentException("Invalid user ID", nameof(id));
    
    if (string.IsNullOrWhiteSpace(dto.Email))
        throw new ArgumentException("Email is required", nameof(dto));
    
    // Additional validation
    if (!IsValidEmail(dto.Email))
        throw new ArgumentException("Invalid email format", nameof(dto));
    
    return await _repository.UpdateAsync(id, dto);
}
```

### Use Parameterized Queries (EF Core does this automatically)
```csharp
// EF Core automatically parameterizes
var user = await _context.Users
    .FirstOrDefaultAsync(u => u.Email == email); // Safe

// If using raw SQL, use parameters
var users = await _context.Users
    .FromSqlRaw("SELECT * FROM Users WHERE Email = {0}", email)
    .ToListAsync(); // Safe
```

### Don't Expose Sensitive Information
```csharp
// Bad: Exposing internal details
catch (Exception ex)
{
    return BadRequest(ex.ToString()); // Exposes stack trace
}

// Good: Generic message to client
catch (Exception ex)
{
    _logger.LogError(ex, "Error processing request");
    return StatusCode(500, "An error occurred");
}
```

### Store Secrets Securely
```csharp
// Bad: Hardcoded secrets
var connectionString = "Server=...;Password=MyPassword123;";

// Good: Use configuration
var connectionString = _configuration.GetConnectionString("DefaultConnection");

// Better: Use User Secrets (development) or Azure Key Vault (production)
```

## Code Comments

### When to Comment
```csharp
// Good: Explain WHY, not WHAT
// Using exponential backoff to handle rate limiting
var delay = TimeSpan.FromSeconds(Math.Pow(2, retryCount));

// Bad: Obvious comment
// Increment i
i++;

// Good: Complex business logic
// Apply 15% discount for premium users, but only on orders over $100
// This is a temporary promotion running until end of Q1
if (user.IsPremium && order.Total > 100)
{
    discount = 0.15m;
}
```

### XML Documentation
```csharp
/// <summary>
/// Creates a new user in the system.
/// </summary>
/// <param name="dto">The user creation data.</param>
/// <returns>The created user.</returns>
/// <exception cref="ValidationException">Thrown when email is invalid.</exception>
public async Task<User> CreateUserAsync(CreateUserDto dto)
{
    // Implementation
}
```

## Configuration Management

### Use Options Pattern
```csharp
// appsettings.json
{
  "EmailSettings": {
    "SmtpServer": "smtp.gmail.com",
    "Port": 587
  }
}

// Settings class
public class EmailSettings
{
    public string SmtpServer { get; set; } = string.Empty;
    public int Port { get; set; }
}

// Registration
builder.Services.Configure<EmailSettings>(
    builder.Configuration.GetSection("EmailSettings"));

// Usage
public class EmailService
{
    private readonly EmailSettings _settings;
    
    public EmailService(IOptions<EmailSettings> options)
    {
        _settings = options.Value;
    }
}
```

### Environment-Specific Configuration
```json
// appsettings.Development.json
{
  "Logging": {
    "LogLevel": {
      "Default": "Debug"
    }
  }
}

// appsettings.Production.json
{
  "Logging": {
    "LogLevel": {
      "Default": "Warning"
    }
  }
}
```

## Logging Guidelines

### Log Levels
```csharp
// Trace: Very detailed, development only
_logger.LogTrace("Entering method with param: {Param}", param);

// Debug: Detailed diagnostic information
_logger.LogDebug("Cache hit for key: {Key}", key);

// Information: General informational messages
_logger.LogInformation("User {UserId} logged in", userId);

// Warning: Potentially harmful situations
_logger.LogWarning("Retry attempt {Attempt} for operation", attempt);

// Error: Error events that might still allow the app to continue
_logger.LogError(ex, "Failed to process order {OrderId}", orderId);

// Critical: Very severe errors causing app failure
_logger.LogCritical(ex, "Database connection failed");
```

### Structured Logging
```csharp
// Good: Structured logging
_logger.LogInformation(
    "Order {OrderId} created for user {UserId} with total {Total}",
    order.Id, 
    order.UserId, 
    order.Total);

// Bad: String interpolation
_logger.LogInformation($"Order {order.Id} created"); // Don't do this
```

## Code Review Checklist

Before submitting code:
- [ ] Follows naming conventions
- [ ] No hardcoded values (use configuration)
- [ ] Proper error handling and logging
- [ ] Async methods use await (not .Result/.Wait())
- [ ] Resources properly disposed
- [ ] No code smells (god classes, long methods, etc.)
- [ ] SOLID principles applied
- [ ] Unit tests written for new code
- [ ] No commented-out code
- [ ] Documentation updated
- [ ] Security concerns addressed
- [ ] Performance considerations reviewed