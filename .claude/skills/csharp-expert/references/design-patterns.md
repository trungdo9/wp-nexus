# C# Design Patterns Reference

Comprehensive guide to common design patterns in C# with practical implementations.

## Creational Patterns

### Singleton Pattern
Ensure a class has only one instance and provide a global point of access to it.

```csharp
public sealed class DatabaseConnection
{
    private static readonly Lazy<DatabaseConnection> _instance = 
        new Lazy<DatabaseConnection>(() => new DatabaseConnection());
    
    public static DatabaseConnection Instance => _instance.Value;
    
    private DatabaseConnection()
    {
        // Private constructor prevents instantiation
    }
    
    public void ExecuteQuery(string query)
    {
        // Implementation
    }
}

// Thread-safe usage
var db = DatabaseConnection.Instance;
db.ExecuteQuery("SELECT * FROM Users");
```

### Factory Pattern
Create objects without specifying the exact class to create.

```csharp
public interface INotification
{
    void Send(string message);
}

public class EmailNotification : INotification
{
    public void Send(string message) => Console.WriteLine($"Email: {message}");
}

public class SmsNotification : INotification
{
    public void Send(string message) => Console.WriteLine($"SMS: {message}");
}

public class NotificationFactory
{
    public static INotification Create(string type)
    {
        return type.ToLower() switch
        {
            "email" => new EmailNotification(),
            "sms" => new SmsNotification(),
            _ => throw new ArgumentException($"Unknown notification type: {type}")
        };
    }
}

// Usage
var notification = NotificationFactory.Create("email");
notification.Send("Hello!");
```

### Builder Pattern
Construct complex objects step by step.

```csharp
public class Pizza
{
    public string Dough { get; set; } = string.Empty;
    public string Sauce { get; set; } = string.Empty;
    public List<string> Toppings { get; set; } = new();
}

public class PizzaBuilder
{
    private readonly Pizza _pizza = new();
    
    public PizzaBuilder WithDough(string dough)
    {
        _pizza.Dough = dough;
        return this;
    }
    
    public PizzaBuilder WithSauce(string sauce)
    {
        _pizza.Sauce = sauce;
        return this;
    }
    
    public PizzaBuilder AddTopping(string topping)
    {
        _pizza.Toppings.Add(topping);
        return this;
    }
    
    public Pizza Build() => _pizza;
}

// Usage
var pizza = new PizzaBuilder()
    .WithDough("Thin crust")
    .WithSauce("Tomato")
    .AddTopping("Cheese")
    .AddTopping("Pepperoni")
    .Build();
```

## Structural Patterns

### Repository Pattern
Abstract data access logic from business logic.

```csharp
public interface IRepository<T> where T : class
{
    Task<T?> GetByIdAsync(int id);
    Task<IEnumerable<T>> GetAllAsync();
    Task<T> AddAsync(T entity);
    Task UpdateAsync(T entity);
    Task DeleteAsync(int id);
}

public class UserRepository : IRepository<User>
{
    private readonly ApplicationDbContext _context;
    
    public UserRepository(ApplicationDbContext context)
    {
        _context = context;
    }
    
    public async Task<User?> GetByIdAsync(int id)
    {
        return await _context.Users.FindAsync(id);
    }
    
    public async Task<IEnumerable<User>> GetAllAsync()
    {
        return await _context.Users.ToListAsync();
    }
    
    public async Task<User> AddAsync(User entity)
    {
        await _context.Users.AddAsync(entity);
        await _context.SaveChangesAsync();
        return entity;
    }
    
    public async Task UpdateAsync(User entity)
    {
        _context.Users.Update(entity);
        await _context.SaveChangesAsync();
    }
    
    public async Task DeleteAsync(int id)
    {
        var user = await GetByIdAsync(id);
        if (user != null)
        {
            _context.Users.Remove(user);
            await _context.SaveChangesAsync();
        }
    }
}
```

### Unit of Work Pattern
Maintain a list of objects affected by a business transaction and coordinate the writing out of changes.

```csharp
public interface IUnitOfWork : IDisposable
{
    IUserRepository Users { get; }
    IOrderRepository Orders { get; }
    Task<int> SaveChangesAsync();
}

public class UnitOfWork : IUnitOfWork
{
    private readonly ApplicationDbContext _context;
    
    public UnitOfWork(ApplicationDbContext context)
    {
        _context = context;
        Users = new UserRepository(_context);
        Orders = new OrderRepository(_context);
    }
    
    public IUserRepository Users { get; }
    public IOrderRepository Orders { get; }
    
    public async Task<int> SaveChangesAsync()
    {
        return await _context.SaveChangesAsync();
    }
    
    public void Dispose()
    {
        _context.Dispose();
    }
}

// Usage in service
public class OrderService
{
    private readonly IUnitOfWork _unitOfWork;
    
    public OrderService(IUnitOfWork unitOfWork)
    {
        _unitOfWork = unitOfWork;
    }
    
    public async Task CreateOrderAsync(CreateOrderDto dto)
    {
        var user = await _unitOfWork.Users.GetByIdAsync(dto.UserId);
        if (user == null) throw new NotFoundException("User not found");
        
        var order = new Order { UserId = dto.UserId, Items = dto.Items };
        await _unitOfWork.Orders.AddAsync(order);
        
        user.OrderCount++;
        await _unitOfWork.Users.UpdateAsync(user);
        
        await _unitOfWork.SaveChangesAsync(); // Single transaction
    }
}
```

### Adapter Pattern
Convert the interface of a class into another interface clients expect.

```csharp
// Third-party library interface (can't modify)
public class LegacyPaymentProcessor
{
    public bool ProcessPayment(string cardNumber, decimal amount)
    {
        Console.WriteLine($"Processing ${amount} with card {cardNumber}");
        return true;
    }
}

// Our desired interface
public interface IPaymentGateway
{
    Task<bool> ProcessAsync(PaymentDetails details);
}

// Adapter
public class PaymentGatewayAdapter : IPaymentGateway
{
    private readonly LegacyPaymentProcessor _processor;
    
    public PaymentGatewayAdapter(LegacyPaymentProcessor processor)
    {
        _processor = processor;
    }
    
    public Task<bool> ProcessAsync(PaymentDetails details)
    {
        var result = _processor.ProcessPayment(details.CardNumber, details.Amount);
        return Task.FromResult(result);
    }
}

// Usage
var adapter = new PaymentGatewayAdapter(new LegacyPaymentProcessor());
await adapter.ProcessAsync(new PaymentDetails("1234", 100m));
```

### Decorator Pattern
Attach additional responsibilities to an object dynamically.

```csharp
public interface INotifier
{
    void Send(string message);
}

public class BasicNotifier : INotifier
{
    public void Send(string message)
    {
        Console.WriteLine($"Basic notification: {message}");
    }
}

public abstract class NotifierDecorator : INotifier
{
    protected readonly INotifier _notifier;
    
    protected NotifierDecorator(INotifier notifier)
    {
        _notifier = notifier;
    }
    
    public virtual void Send(string message)
    {
        _notifier.Send(message);
    }
}

public class EmailDecorator : NotifierDecorator
{
    public EmailDecorator(INotifier notifier) : base(notifier) { }
    
    public override void Send(string message)
    {
        base.Send(message);
        Console.WriteLine($"Email sent: {message}");
    }
}

public class SmsDecorator : NotifierDecorator
{
    public SmsDecorator(INotifier notifier) : base(notifier) { }
    
    public override void Send(string message)
    {
        base.Send(message);
        Console.WriteLine($"SMS sent: {message}");
    }
}

// Usage - compose behaviors
INotifier notifier = new BasicNotifier();
notifier = new EmailDecorator(notifier);
notifier = new SmsDecorator(notifier);
notifier.Send("Important alert!");
// Outputs: Basic, Email, SMS
```

## Behavioral Patterns

### Strategy Pattern
Define a family of algorithms, encapsulate each one, and make them interchangeable.

```csharp
public interface IPaymentStrategy
{
    Task<bool> PayAsync(decimal amount);
}

public class CreditCardPayment : IPaymentStrategy
{
    private readonly string _cardNumber;
    
    public CreditCardPayment(string cardNumber)
    {
        _cardNumber = cardNumber;
    }
    
    public async Task<bool> PayAsync(decimal amount)
    {
        Console.WriteLine($"Paid ${amount} with credit card {_cardNumber}");
        return await Task.FromResult(true);
    }
}

public class PayPalPayment : IPaymentStrategy
{
    private readonly string _email;
    
    public PayPalPayment(string email)
    {
        _email = email;
    }
    
    public async Task<bool> PayAsync(decimal amount)
    {
        Console.WriteLine($"Paid ${amount} via PayPal ({_email})");
        return await Task.FromResult(true);
    }
}

public class PaymentContext
{
    private IPaymentStrategy _strategy;
    
    public void SetStrategy(IPaymentStrategy strategy)
    {
        _strategy = strategy;
    }
    
    public async Task<bool> ExecutePaymentAsync(decimal amount)
    {
        return await _strategy.PayAsync(amount);
    }
}

// Usage
var context = new PaymentContext();
context.SetStrategy(new CreditCardPayment("1234"));
await context.ExecutePaymentAsync(100m);

context.SetStrategy(new PayPalPayment("user@example.com"));
await context.ExecutePaymentAsync(50m);
```

### Observer Pattern
Define a one-to-many dependency between objects so that when one object changes state, all its dependents are notified.

```csharp
public interface IObserver
{
    void Update(string message);
}

public interface ISubject
{
    void Attach(IObserver observer);
    void Detach(IObserver observer);
    void Notify(string message);
}

public class NewsPublisher : ISubject
{
    private readonly List<IObserver> _observers = new();
    
    public void Attach(IObserver observer)
    {
        _observers.Add(observer);
    }
    
    public void Detach(IObserver observer)
    {
        _observers.Remove(observer);
    }
    
    public void Notify(string message)
    {
        foreach (var observer in _observers)
        {
            observer.Update(message);
        }
    }
    
    public void PublishNews(string news)
    {
        Console.WriteLine($"Breaking: {news}");
        Notify(news);
    }
}

public class EmailSubscriber : IObserver
{
    private readonly string _email;
    
    public EmailSubscriber(string email)
    {
        _email = email;
    }
    
    public void Update(string message)
    {
        Console.WriteLine($"Email to {_email}: {message}");
    }
}

// Usage
var publisher = new NewsPublisher();
var subscriber1 = new EmailSubscriber("user1@example.com");
var subscriber2 = new EmailSubscriber("user2@example.com");

publisher.Attach(subscriber1);
publisher.Attach(subscriber2);
publisher.PublishNews("New feature released!");
```

### Command Pattern
Encapsulate a request as an object, thereby letting you parameterize clients with different requests, queue or log requests.

```csharp
public interface ICommand
{
    void Execute();
    void Undo();
}

public class Document
{
    public string Content { get; private set; } = string.Empty;
    
    public void Write(string text)
    {
        Content += text;
    }
    
    public void Erase(int length)
    {
        if (length > Content.Length) length = Content.Length;
        Content = Content[..^length];
    }
}

public class WriteCommand : ICommand
{
    private readonly Document _document;
    private readonly string _text;
    
    public WriteCommand(Document document, string text)
    {
        _document = document;
        _text = text;
    }
    
    public void Execute()
    {
        _document.Write(_text);
    }
    
    public void Undo()
    {
        _document.Erase(_text.Length);
    }
}

public class CommandHistory
{
    private readonly Stack<ICommand> _history = new();
    
    public void Execute(ICommand command)
    {
        command.Execute();
        _history.Push(command);
    }
    
    public void Undo()
    {
        if (_history.Count > 0)
        {
            var command = _history.Pop();
            command.Undo();
        }
    }
}

// Usage
var document = new Document();
var history = new CommandHistory();

history.Execute(new WriteCommand(document, "Hello "));
history.Execute(new WriteCommand(document, "World!"));
Console.WriteLine(document.Content); // "Hello World!"

history.Undo();
Console.WriteLine(document.Content); // "Hello "
```

### Chain of Responsibility Pattern
Avoid coupling the sender of a request to its receiver by giving more than one object a chance to handle the request.

```csharp
public abstract class Handler
{
    protected Handler? _nextHandler;
    
    public Handler SetNext(Handler handler)
    {
        _nextHandler = handler;
        return handler;
    }
    
    public abstract string Handle(string request);
}

public class AuthenticationHandler : Handler
{
    public override string Handle(string request)
    {
        if (request.Contains("authenticated"))
        {
            return _nextHandler?.Handle(request) ?? request;
        }
        return "Authentication failed";
    }
}

public class AuthorizationHandler : Handler
{
    public override string Handle(string request)
    {
        if (request.Contains("authorized"))
        {
            return _nextHandler?.Handle(request) ?? request;
        }
        return "Authorization failed";
    }
}

public class ValidationHandler : Handler
{
    public override string Handle(string request)
    {
        if (request.Contains("valid"))
        {
            return "Request processed successfully";
        }
        return "Validation failed";
    }
}

// Usage
var auth = new AuthenticationHandler();
var authz = new AuthorizationHandler();
var validation = new ValidationHandler();

auth.SetNext(authz).SetNext(validation);

Console.WriteLine(auth.Handle("authenticated authorized valid")); // Success
Console.WriteLine(auth.Handle("authenticated invalid")); // Validation failed
```

## Modern C# Patterns

### Options Pattern
Configure application settings using strongly-typed classes.

```csharp
// appsettings.json
{
  "EmailSettings": {
    "SmtpServer": "smtp.gmail.com",
    "Port": 587,
    "SenderEmail": "noreply@example.com"
  }
}

// Configuration class
public class EmailSettings
{
    public string SmtpServer { get; set; } = string.Empty;
    public int Port { get; set; }
    public string SenderEmail { get; set; } = string.Empty;
}

// Registration in Program.cs
builder.Services.Configure<EmailSettings>(
    builder.Configuration.GetSection("EmailSettings"));

// Usage in service
public class EmailService
{
    private readonly EmailSettings _settings;
    
    public EmailService(IOptions<EmailSettings> options)
    {
        _settings = options.Value;
    }
    
    public void SendEmail(string to, string subject, string body)
    {
        // Use _settings.SmtpServer, _settings.Port, etc.
    }
}
```

### Specification Pattern
Encapsulate business rules into reusable specifications.

```csharp
public abstract class Specification<T>
{
    public abstract bool IsSatisfiedBy(T entity);
    
    public Specification<T> And(Specification<T> other)
    {
        return new AndSpecification<T>(this, other);
    }
    
    public Specification<T> Or(Specification<T> other)
    {
        return new OrSpecification<T>(this, other);
    }
}

public class AndSpecification<T> : Specification<T>
{
    private readonly Specification<T> _left;
    private readonly Specification<T> _right;
    
    public AndSpecification(Specification<T> left, Specification<T> right)
    {
        _left = left;
        _right = right;
    }
    
    public override bool IsSatisfiedBy(T entity)
    {
        return _left.IsSatisfiedBy(entity) && _right.IsSatisfiedBy(entity);
    }
}

// Concrete specifications
public class ActiveUserSpecification : Specification<User>
{
    public override bool IsSatisfiedBy(User user)
    {
        return user.IsActive;
    }
}

public class PremiumUserSpecification : Specification<User>
{
    public override bool IsSatisfiedBy(User user)
    {
        return user.IsPremium;
    }
}

// Usage
var activePremiumSpec = new ActiveUserSpecification()
    .And(new PremiumUserSpecification());

var eligibleUsers = users.Where(u => activePremiumSpec.IsSatisfiedBy(u));
```

## Anti-Patterns to Avoid

### God Object
**Problem**: Single class that does too much.
**Solution**: Follow Single Responsibility Principle, split into focused classes.

### Spaghetti Code
**Problem**: Unstructured flow, hard to follow.
**Solution**: Use clear methods, avoid deep nesting, apply design patterns.

### Magic Numbers/Strings
**Problem**: Hardcoded values without explanation.
**Solution**: Use named constants or configuration.

```csharp
// Bad
if (user.Type == 1) { }

// Good
public enum UserType { Regular = 1, Premium = 2 }
if (user.Type == UserType.Premium) { }
```

### Premature Optimization
**Problem**: Optimizing before identifying bottlenecks.
**Solution**: Profile first, optimize what matters, keep code readable.