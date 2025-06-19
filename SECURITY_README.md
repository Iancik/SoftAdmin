# Protecție de Securitate - SoftAdmin

## Descriere
Această aplicație web are implementate măsuri de securitate pentru a preveni accesul direct la fișierele PHP din directoarele sensibile.

## Fișiere de Protecție

### 1. `.htaccess` (directorul rădăcină)
- Blochează accesul direct la fișierele PHP din directoarele:
  - `pages/`
  - `api/`
  - `includes/`
  - `config/`
  - `js/`
  - `softadmin-desktop/`
- Blochează accesul la fișierele de configurare: `config.php`, `auth.php`
- Blochează accesul la fișierele de log și fișierele sistem
- Permite accesul doar la fișierele publice: `index.php`, `login.php`, `logout.php`

### 2. Fișiere `.htaccess` în directoarele sensibile
- `pages/.htaccess` - Blochează complet accesul direct
- `api/.htaccess` - Blochează complet accesul direct
- `includes/.htaccess` - Blochează complet accesul direct
- `config/.htaccess` - Blochează complet accesul direct
- `js/.htaccess` - Blochează accesul la fișierele PHP
- `softadmin-desktop/.htaccess` - Blochează complet accesul direct

## Cum Funcționează

✅ http://yourdomain.com/index.php (ACCES PERMIS)
✅ http://yourdomain.com/index.php?action=dashboard (ACCES PRIN INDEX)
✅ http://yourdomain.com/login.php (ACCES PERMIS)
```

## Măsuri Suplimentare de Securitate

1. **Dezactivarea listării directoarelor** - `Options -Indexes`
2. **Ascunderea informațiilor despre server** - `ServerTokens Prod`
3. **Protecție împotriva XSS** - Headers de securitate
4. **Validarea parametrilor** - Doar acțiuni valide sunt permise

## Testarea Protecției

Pentru a testa că protecția funcționează:

1. Încearcă să accesezi direct: `http://yourdomain.com/pages/norme/dashboard.php`
2. Ar trebui să primești o eroare 403 Forbidden
3. Accesul prin `http://yourdomain.com/index.php?action=dashboard` ar trebui să funcționeze

## Notă Importantă

Această protecție funcționează doar pe servere Apache care suportă fișiere `.htaccess`. Pentru servere Nginx sau alte tipuri de servere, regulile trebuie configurate în fișierul de configurare al serverului.

## Pentru Nginx

Dacă folosești Nginx, adaugă următoarele reguli în configurația serverului:

```nginx
location ~ ^/(pages|api|includes|config|js|softadmin-desktop)/.*\.php$ {
    deny all;
    return 403;
}

location ~ ^/(config\.php|auth\.php)$ {
    deny all;
    return 403;
}
``` 