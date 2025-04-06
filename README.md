# Passw
## Introduction
**A PHP-powered CLI password wrangler that’ll stash your secret codes in its brain, locked tighter than a squirrel’s nut vault during a zombie apocalypse!**

- **Version**: 1.0.0
- **Features**: Add, view, update, delete, and list service credentials.

## Prerequisites
Before using the tool, ensure you have:
- **PHP**: Version 5.6 or higher installed with CLI support.
- **GnuPG**: Installed on your system. [Installation guide](installation-guide-gnupg.md)
- **PHP-GnuPG Extension**: Enabled in PHP.
- **Terminal Access**: A command-line interface (e.g., Terminal on macOS/Linux, Command Prompt on Windows).
- **Write Permissions**: Access to a directory for storing data.


## Installation

### Step 1: Install Dependencies
1. **PHP**: Download and install from [php.net](https://www.php.net/downloads.php) or use a package manager:
   - Ubuntu: `sudo apt install php php-dev php-pear`
   - CentOS: `sudo yum install php php-devel php-pear`
   - macOS: `brew install php`
2. **GnuPG**: Install based on your OS:
   - Ubuntu: `sudo apt install gnupg`
   - CentOS: `sudo yum install gnupg`
   - macOS: `brew install gnupg`
   - Windows: Install Gpg4win from [gpg4win.org](https://www.gpg4win.org/).
3. **PHP-GnuPG Extension**:
   - Install via PECL: `sudo pecl install gnupg`
   - Enable in `php.ini` (e.g., `/etc/php/8.1/cli/php.ini`):
     ```
     extension=gnupg.so  # Unix
     extension=php_gnupg.dll  # Windows
     ```
   - Verify: `php -m | grep gnupg`

### Step 2: Set Up the Tool
1. **Download the Script**:
```bash
git clone https://github.com/Amsiam0/passw.git
```
   - Save the provided PHP code as `main.php`.
   - Create `config.php` with:
     ```php
     <?php
     define('HOME_DIR', '/path/to/your/storage/directory');
     ```
   - Ensure `HOME_DIR` is writable (e.g., `chmod 0700 /path/to/your/storage/directory`).
2. **Initialize the Tool**:
   - Run: `php main.php`
   - If no `database.json` exists, you’ll be prompted to:
     - Enter a username (e.g., "John Doe").
     - Enter an email (e.g., "john@example.com").
     - Enter a passphrase (remember this; it’s required for decryption).

   This creates:
   - `database.json`: Stores your user ID and email.
   - `<HOME_DIR>/config/`: Contains `public_key.asc` and `private_key.asc`.


## Usage

### Basic Command Syntax
Run commands using:
```
php main.php <command>
```

### Available Commands
| Command                  | Description                     |
|--------------------------|---------------------------------|
| `help`, `-h`             | Show help message              |
| `new`, `add`, `-a`       | Add a new service              |
| `show`, `view`, `-s`     | View a service’s credentials   |
| `update`, `edit`, `-u`   | Update a service               |
| `delete`, `remove`, `-d` | Delete a service               |
| `list`, `ls`, `-l`       | List all services              |

### Examples

#### Initialize the Tool
```
php main.php
```
- Follow prompts to set up your GPG keys and database.

#### Add a Service
```
php main.php add
Enter the service name: Gmail
Enter the username: john.doe
Enter the password: mysecretpass
```
- Output: `Service added successfully.`

#### View a Service
```
php main.php show
Enter the service name: Gmail
Enter your passphrase (attempt 1/3): mypassphrase
```
- Output:
  ```
  Service: Gmail
  Username: john.doe
  Password: mysecretpass
  ```

#### Update a Service
```
php main.php update
Enter the service name: Gmail
Enter the username: john.doe2
Enter the password: newsecretpass
```
- Output: `Service updated successfully.`

#### Delete a Service
```
php main.php delete
Enter the service name: Gmail
```
- Output: `Service deleted successfully.`

#### List Services
```
php main.php list
```
- Output: `Services: - Gmail - Facebook` (if services exist).

## Security Tips
- **Passphrase**: Keep your passphrase secret and memorable. You’ll need it to view or update services (3 attempts allowed).
- **Storage**: Protect `<HOME_DIR>` (e.g., set permissions to 0700 on Unix systems).
- **Backups**: Regularly back up `<HOME_DIR>/config/` and `database.json`.

## Troubleshooting
- **“GPG is not installed”**: Install GnuPG (see Installation).
- **“Encryption/Decryption failed”**: Check GPG keys and passphrase.
- **“Service name does not exist”**: Ensure the service was added and spelled correctly (case-insensitive).
- **“PHP-GnuPG not found”**: Verify the extension is enabled (`php -m`).

## Notes
- Service names must be alphanumeric with underscores (e.g., "Gmail", "My_Service_1").
- The tool stores encrypted data in `<HOME_DIR>/<service>/data.gpg`.
- If you lose your passphrase or keys, your data cannot be recovered.

## Support
For issues:
- Run `php main.php -h` for command help.
- Check system logs or GPG output for detailed errors.
- Contact your system administrator if needed.
