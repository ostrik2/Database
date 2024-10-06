# Database Class Description

Class for working with MySQL database using PDO.
Provides methods for connection, query execution and error handling.

1. **Database Connection**:

   - Connection is made via PDO with parameters that are set in the constructor.
   - Error handling through exceptions (`PDO::ERRMODE_EXCEPTION`) is configured.
   - Database encoding is `utf8mb4` to support the full range of Unicode characters.

2. **Methods**:

   - `query()`: Executes a generic SQL query with the ability to use prepared statements.
   - `select()`: Executes a SELECT type query and returns an array of data.
   - `execute()`: Executes a query of type INSERT/UPDATE/DELETE and returns the number of rows affected.
   - `lastInsertId()`: Returns the ID of the last record inserted.
   - `isConnected()`: Checks if the connection is established.
   - `getErrorMessage()`: Returns an error message if an error occurred.
   - `disconnect()`: Closes the database connection.

3. **Security**:

   - All queries are executed through prepared expressions, which prevents SQL injection.
   - Error messages are handled and thrown as exceptions to avoid information leaks.

4. **Memory and Resources**:

   - In the class destructor, the connection is automatically closed to free resources.

## Example Usage

```php
try {
  // Create a database object
  $db = new Database('localhost', 'my_database', 'username', 'password');

  // Execute SELECT query
  $users = $db->select("SELECT * FROM users WHERE age > ?", [25]);

  // Output the result
  foreach ($users as $user) {
    echo $user['name'] . " - " . $user['email'] . "<br>";
  }

  // Insert a new record
  $db->execute("INSERT INTO users (name, email, age) VALUES (?, ?, ?)", ['John Doe', 'john@example.com', 30]);
  echo "Inserted ID: " . $db->lastInsertId();

} catch (Exception $e) {
  echo "Error: " . $e->getMessage();
}
