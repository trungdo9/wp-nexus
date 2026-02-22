# Common C# Code Snippets

Frequently used code patterns and utilities for C# development.

## String Utilities

### String Extension Methods
```csharp
public static class StringExtensions
{
    public static bool IsNullOrWhiteSpace(this string? value)
    {
        return string.IsNullOrWhiteSpace(value);
    }
    
    public static string Truncate(this string value, int maxLength)
    {
        if (string.IsNullOrEmpty(value)) return value;
        return value.Length <= maxLength ? value : value[..maxLength];
    }
    
    public static string ToTitleCase(this string value)
    {
        if (string.IsNullOrEmpty(value)) return value;
        var textInfo = CultureInfo.CurrentCulture.TextInfo;
        return textInfo.ToTitleCase(value.ToLower());
    }
    
    public static string RemoveWhitespace(this string value)
    {
        return new string(value.Where(c => !char.IsWhiteSpace(c)).ToArray());
    }
    
    public static bool ContainsAny(this string value, params string[] values)
    {
        return values.Any(v => value.Contains(v, StringComparison.OrdinalIgnoreCase));
    }
}

// Usage
var text = "  hello world  ";
var truncated = text.Truncate(5); // "  hel"
var title = "hello world".ToTitleCase(); // "Hello World"
```

## Collection Utilities

### LINQ Extensions
```csharp
public static class EnumerableExtensions
{
    public static bool IsNullOrEmpty<T>(this IEnumerable<T>? collection)
    {
        return collection == null || !collection.Any();
    }
    
    public static IEnumerable<T> WhereNotNull<T>(this IEnumerable<T?> collection) where T : class
    {
        return collection.Where(item => item != null)!;
    }
    
    public static IEnumerable<IEnumerable<T>> Batch<T>(this IEnumerable<T> source, int size)
    {
        T[]? bucket = null;
        var count = 0;
        
        foreach (var item in source)
        {
            bucket ??= new T[size];
            bucket[count++] = item;
            
            if (count != size) continue;
            
            yield return bucket;
            bucket = null;
            count = 0;
        }
        
        if (bucket != null && count > 0)
        {
            Array.Resize(ref bucket, count);
            yield return bucket;
        }
    }
    
    public static T? FirstOrDefault<T>(this IEnumerable<T> source, T? defaultValue)
    {
        return source.DefaultIfEmpty(defaultValue).First();
    }
}

// Usage
var numbers = new[] { 1, 2, 3, 4, 5 };
var batches = numbers.Batch(2); // [[1,2], [3,4], [5]]

var items = GetItems().WhereNotNull();
```

### Dictionary Helpers
```csharp
public static class DictionaryExtensions
{
    public static TValue GetOrAdd<TKey, TValue>(
        this Dictionary<TKey, TValue> dict, 
        TKey key, 
        Func<TValue> valueFactory) where TKey : notnull
    {
        if (!dict.TryGetValue(key, out var value))
        {
            value = valueFactory();
            dict[key] = value;
        }
        return value;
    }
    
    public static TValue? GetValueOrDefault<TKey, TValue>(
        this Dictionary<TKey, TValue> dict, 
        TKey key, 
        TValue? defaultValue = default) where TKey : notnull
    {
        return dict.TryGetValue(key, out var value) ? value : defaultValue;
    }
}

// Usage
var cache = new Dictionary<string, UserData>();
var user = cache.GetOrAdd("user123", () => LoadUserData("user123"));
```

## Async Utilities

### Retry Logic
```csharp
public static class RetryHelper
{
    public static async Task<T> RetryAsync<T>(
        Func<Task<T>> operation,
        int maxRetries = 3,
        TimeSpan? delay = null)
    {
        delay ??= TimeSpan.FromSeconds(1);
        
        for (int i = 0; i < maxRetries; i++)
        {
            try
            {
                return await operation();
            }
            catch (Exception ex) when (i < maxRetries - 1)
            {
                await Task.Delay(delay.Value);
            }
        }
        
        return await operation(); // Final attempt, let exception bubble up
    }
    
    public static async Task<T> RetryWithExponentialBackoffAsync<T>(
        Func<Task<T>> operation,
        int maxRetries = 3,
        TimeSpan? initialDelay = null)
    {
        initialDelay ??= TimeSpan.FromSeconds(1);
        
        for (int i = 0; i < maxRetries; i++)
        {
            try
            {
                return await operation();
            }
            catch (Exception) when (i < maxRetries - 1)
            {
                var delay = TimeSpan.FromMilliseconds(
                    initialDelay.Value.TotalMilliseconds * Math.Pow(2, i));
                await Task.Delay(delay);
            }
        }
        
        return await operation();
    }
}

// Usage
var result = await RetryHelper.RetryAsync(
    async () => await CallExternalApiAsync(),
    maxRetries: 5,
    delay: TimeSpan.FromSeconds(2)
);
```

### Timeout Wrapper
```csharp
public static class TimeoutHelper
{
    public static async Task<T> WithTimeoutAsync<T>(
        this Task<T> task,
        TimeSpan timeout)
    {
        using var cts = new CancellationTokenSource();
        var delayTask = Task.Delay(timeout, cts.Token);
        var completedTask = await Task.WhenAny(task, delayTask);
        
        if (completedTask == delayTask)
        {
            throw new TimeoutException($"Operation timed out after {timeout.TotalSeconds} seconds");
        }
        
        cts.Cancel();
        return await task;
    }
}

// Usage
var result = await LongRunningOperationAsync()
    .WithTimeoutAsync(TimeSpan.FromSeconds(30));
```

### Parallel Processing with Throttling
```csharp
public static class ParallelHelper
{
    public static async Task<IEnumerable<TResult>> ProcessInParallelAsync<TSource, TResult>(
        IEnumerable<TSource> items,
        Func<TSource, Task<TResult>> processFunc,
        int maxDegreeOfParallelism = 10)
    {
        using var semaphore = new SemaphoreSlim(maxDegreeOfParallelism);
        
        var tasks = items.Select(async item =>
        {
            await semaphore.WaitAsync();
            try
            {
                return await processFunc(item);
            }
            finally
            {
                semaphore.Release();
            }
        });
        
        return await Task.WhenAll(tasks);
    }
}

// Usage
var userIds = Enumerable.Range(1, 100);
var users = await ParallelHelper.ProcessInParallelAsync(
    userIds,
    async id => await GetUserAsync(id),
    maxDegreeOfParallelism: 5
);
```

## Validation Utilities

### Guard Clauses
```csharp
public static class Guard
{
    public static void AgainstNull<T>(T? value, string parameterName) where T : class
    {
        if (value == null)
        {
            throw new ArgumentNullException(parameterName);
        }
    }
    
    public static void AgainstNullOrEmpty(string? value, string parameterName)
    {
        if (string.IsNullOrWhiteSpace(value))
        {
            throw new ArgumentException("Value cannot be null or empty", parameterName);
        }
    }
    
    public static void AgainstNegative(int value, string parameterName)
    {
        if (value < 0)
        {
            throw new ArgumentOutOfRangeException(parameterName, "Value cannot be negative");
        }
    }
    
    public static void AgainstOutOfRange(int value, int min, int max, string parameterName)
    {
        if (value < min || value > max)
        {
            throw new ArgumentOutOfRangeException(
                parameterName, 
                $"Value must be between {min} and {max}");
        }
    }
}

// Usage
public void ProcessOrder(Order order, int quantity)
{
    Guard.AgainstNull(order, nameof(order));
    Guard.AgainstNegative(quantity, nameof(quantity));
    
    // Process order
}
```

### FluentValidation Example
```csharp
public class CreateUserValidator : AbstractValidator<CreateUserDto>
{
    public CreateUserValidator()
    {
        RuleFor(x => x.Name)
            .NotEmpty().WithMessage("Name is required")
            .Length(2, 100).WithMessage("Name must be between 2 and 100 characters");
            
        RuleFor(x => x.Email)
            .NotEmpty().WithMessage("Email is required")
            .EmailAddress().WithMessage("Invalid email format");
            
        RuleFor(x => x.Age)
            .GreaterThanOrEqualTo(18).WithMessage("Must be 18 or older")
            .LessThan(150).WithMessage("Invalid age");
            
        RuleFor(x => x.Password)
            .NotEmpty()
            .MinimumLength(8)
            .Matches(@"[A-Z]").WithMessage("Password must contain uppercase")
            .Matches(@"[a-z]").WithMessage("Password must contain lowercase")
            .Matches(@"[0-9]").WithMessage("Password must contain number");
    }
}

// Usage
var validator = new CreateUserValidator();
var result = await validator.ValidateAsync(dto);

if (!result.IsValid)
{
    var errors = result.Errors.Select(e => e.ErrorMessage);
    throw new ValidationException(string.Join(", ", errors));
}
```

## Logging Patterns

### Structured Logging
```csharp
public class UserService
{
    private readonly ILogger<UserService> _logger;
    
    public UserService(ILogger<UserService> logger)
    {
        _logger = logger;
    }
    
    public async Task<User> CreateUserAsync(CreateUserDto dto)
    {
        _logger.LogInformation(
            "Creating user with email {Email}", 
            dto.Email);
        
        try
        {
            var user = await _repository.CreateAsync(dto);
            
            _logger.LogInformation(
                "User {UserId} created successfully", 
                user.Id);
            
            return user;
        }
        catch (Exception ex)
        {
            _logger.LogError(
                ex, 
                "Failed to create user with email {Email}", 
                dto.Email);
            throw;
        }
    }
}
```

### Performance Logging
```csharp
public static class PerformanceLogger
{
    public static async Task<T> LogExecutionTimeAsync<T>(
        ILogger logger,
        string operationName,
        Func<Task<T>> operation)
    {
        var stopwatch = Stopwatch.StartNew();
        
        try
        {
            var result = await operation();
            stopwatch.Stop();
            
            logger.LogInformation(
                "{Operation} completed in {ElapsedMs}ms",
                operationName,
                stopwatch.ElapsedMilliseconds);
            
            return result;
        }
        catch (Exception ex)
        {
            stopwatch.Stop();
            
            logger.LogError(
                ex,
                "{Operation} failed after {ElapsedMs}ms",
                operationName,
                stopwatch.ElapsedMilliseconds);
            
            throw;
        }
    }
}

// Usage
var users = await PerformanceLogger.LogExecutionTimeAsync(
    _logger,
    "GetAllUsers",
    async () => await _repository.GetAllAsync()
);
```

## Caching Patterns

### In-Memory Cache Helper
```csharp
public class CacheService
{
    private readonly IMemoryCache _cache;
    private readonly ILogger<CacheService> _logger;
    
    public CacheService(IMemoryCache cache, ILogger<CacheService> logger)
    {
        _cache = cache;
        _logger = logger;
    }
    
    public async Task<T> GetOrCreateAsync<T>(
        string key,
        Func<Task<T>> factory,
        TimeSpan? expiration = null)
    {
        if (_cache.TryGetValue(key, out T cachedValue))
        {
            _logger.LogDebug("Cache hit for key {Key}", key);
            return cachedValue;
        }
        
        _logger.LogDebug("Cache miss for key {Key}", key);
        
        var value = await factory();
        
        var cacheOptions = new MemoryCacheEntryOptions
        {
            AbsoluteExpirationRelativeToNow = expiration ?? TimeSpan.FromMinutes(5)
        };
        
        _cache.Set(key, value, cacheOptions);
        
        return value;
    }
    
    public void Remove(string key)
    {
        _cache.Remove(key);
        _logger.LogDebug("Removed cache key {Key}", key);
    }
}

// Usage
var user = await _cacheService.GetOrCreateAsync(
    $"user:{userId}",
    async () => await _userRepository.GetByIdAsync(userId),
    TimeSpan.FromMinutes(10)
);
```

## DateTime Utilities

### DateTime Extensions
```csharp
public static class DateTimeExtensions
{
    public static bool IsWeekend(this DateTime date)
    {
        return date.DayOfWeek == DayOfWeek.Saturday || 
               date.DayOfWeek == DayOfWeek.Sunday;
    }
    
    public static DateTime StartOfDay(this DateTime date)
    {
        return date.Date;
    }
    
    public static DateTime EndOfDay(this DateTime date)
    {
        return date.Date.AddDays(1).AddTicks(-1);
    }
    
    public static DateTime StartOfWeek(this DateTime date, DayOfWeek startDay = DayOfWeek.Monday)
    {
        var diff = (7 + (date.DayOfWeek - startDay)) % 7;
        return date.AddDays(-1 * diff).Date;
    }
    
    public static DateTime StartOfMonth(this DateTime date)
    {
        return new DateTime(date.Year, date.Month, 1);
    }
    
    public static int Age(this DateTime birthDate)
    {
        var today = DateTime.Today;
        var age = today.Year - birthDate.Year;
        if (birthDate.Date > today.AddYears(-age)) age--;
        return age;
    }
}

// Usage
var today = DateTime.Now;
var isWeekend = today.IsWeekend();
var age = birthDate.Age();
var weekStart = today.StartOfWeek();
```

## File I/O Utilities

### Safe File Operations
```csharp
public static class FileHelper
{
    public static async Task<string> ReadAllTextSafeAsync(string filePath)
    {
        if (!File.Exists(filePath))
        {
            throw new FileNotFoundException($"File not found: {filePath}");
        }
        
        return await File.ReadAllTextAsync(filePath);
    }
    
    public static async Task WriteAllTextSafeAsync(
        string filePath, 
        string content, 
        bool createDirectory = true)
    {
        if (createDirectory)
        {
            var directory = Path.GetDirectoryName(filePath);
            if (!string.IsNullOrEmpty(directory))
            {
                Directory.CreateDirectory(directory);
            }
        }
        
        await File.WriteAllTextAsync(filePath, content);
    }
    
    public static string SanitizeFileName(string fileName)
    {
        var invalidChars = Path.GetInvalidFileNameChars();
        return new string(fileName
            .Where(c => !invalidChars.Contains(c))
            .ToArray());
    }
}
```

## JSON Utilities

### JSON Serialization Helpers
```csharp
public static class JsonHelper
{
    private static readonly JsonSerializerOptions DefaultOptions = new()
    {
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase,
        WriteIndented = true,
        DefaultIgnoreCondition = JsonIgnoreCondition.WhenWritingNull
    };
    
    public static string Serialize<T>(T obj, JsonSerializerOptions? options = null)
    {
        return JsonSerializer.Serialize(obj, options ?? DefaultOptions);
    }
    
    public static T? Deserialize<T>(string json, JsonSerializerOptions? options = null)
    {
        return JsonSerializer.Deserialize<T>(json, options ?? DefaultOptions);
    }
    
    public static async Task<T?> DeserializeFileAsync<T>(string filePath)
    {
        using var stream = File.OpenRead(filePath);
        return await JsonSerializer.DeserializeAsync<T>(stream, DefaultOptions);
    }
    
    public static async Task SerializeToFileAsync<T>(T obj, string filePath)
    {
        using var stream = File.Create(filePath);
        await JsonSerializer.SerializeAsync(stream, obj, DefaultOptions);
    }
}
```

## HTTP Client Utilities

### Typed HttpClient Helper
```csharp
public class ApiClient
{
    private readonly HttpClient _httpClient;
    private readonly ILogger<ApiClient> _logger;
    
    public ApiClient(HttpClient httpClient, ILogger<ApiClient> logger)
    {
        _httpClient = httpClient;
        _logger = logger;
    }
    
    public async Task<T?> GetAsync<T>(string url)
    {
        try
        {
            var response = await _httpClient.GetAsync(url);
            response.EnsureSuccessStatusCode();
            
            var content = await response.Content.ReadAsStringAsync();
            return JsonSerializer.Deserialize<T>(content);
        }
        catch (HttpRequestException ex)
        {
            _logger.LogError(ex, "HTTP request failed for URL: {Url}", url);
            throw;
        }
    }
    
    public async Task<TResponse?> PostAsync<TRequest, TResponse>(
        string url, 
        TRequest data)
    {
        try
        {
            var json = JsonSerializer.Serialize(data);
            var content = new StringContent(json, Encoding.UTF8, "application/json");
            
            var response = await _httpClient.PostAsync(url, content);
            response.EnsureSuccessStatusCode();
            
            var responseContent = await response.Content.ReadAsStringAsync();
            return JsonSerializer.Deserialize<TResponse>(responseContent);
        }
        catch (HttpRequestException ex)
        {
            _logger.LogError(ex, "HTTP POST failed for URL: {Url}", url);
            throw;
        }
    }
}

// Registration in Program.cs
builder.Services.AddHttpClient<ApiClient>(client =>
{
    client.BaseAddress = new Uri("https://api.example.com");
    client.DefaultRequestHeaders.Add("Accept", "application/json");
    client.Timeout = TimeSpan.FromSeconds(30);
});
```

## Pagination Helper

### Paginated List
```csharp
public class PagedResult<T>
{
    public List<T> Items { get; set; } = new();
    public int PageNumber { get; set; }
    public int PageSize { get; set; }
    public int TotalCount { get; set; }
    public int TotalPages => (int)Math.Ceiling(TotalCount / (double)PageSize);
    public bool HasPrevious => PageNumber > 1;
    public bool HasNext => PageNumber < TotalPages;
}

public static class PaginationExtensions
{
    public static async Task<PagedResult<T>> ToPagedResultAsync<T>(
        this IQueryable<T> query,
        int pageNumber,
        int pageSize)
    {
        var count = await query.CountAsync();
        
        var items = await query
            .Skip((pageNumber - 1) * pageSize)
            .Take(pageSize)
            .ToListAsync();
        
        return new PagedResult<T>
        {
            Items = items,
            PageNumber = pageNumber,
            PageSize = pageSize,
            TotalCount = count
        };
    }
}

// Usage
var pagedUsers = await _context.Users
    .Where(u => u.IsActive)
    .OrderBy(u => u.Name)
    .ToPagedResultAsync(pageNumber: 1, pageSize: 20);
```