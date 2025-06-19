# Configurarea Bazei de Date - SoftAdmin

## 🔒 Securitate

**IMPORTANT:** Fișierele cu configurațiile bazei de date sunt excluse din Git pentru securitate. Nu vor fi sincronizate cu repository-ul.

## 📁 Fișiere de Configurare

Aplicația folosește 3 fișiere de configurare pentru baza de date:

1. **`config.php`** (directorul rădăcină)
2. **`config/database.php`**
3. **`includes/config.php`**

## 🚀 Instalare și Configurare

### Pasul 1: Copiază fișierele de exemplu

```bash
# Copiază fișierele de exemplu
cp config.php.example config.php
cp config/database.php.example config/database.php
cp includes/config.php.example includes/config.php
```

### Pasul 2: Completează configurațiile

Editează fiecare fișier și înlocuiește valorile cu datele tale:

#### config.php
```php
define('DB_HOST', 'your_host_here');        
define('DB_NAME', 'your_database_name');    
define('DB_USER', 'your_username');         
define('DB_PASS', 'your_password');         
```

#### config/database.php
```php
$host = 'your_host_here';        
$dbname = 'your_database_name'; 
$username = 'your_username';     
$password = 'your_password';     

#### includes/config.php
```php
$db_host = 'your_host_here';     
$db_name = 'your_database_name'; 
$db_user = 'your_username';      
$db_pass = 'your_password';      
```

