
# Installation Guide for GnuPG and PHP-GnuPG

## Overview
- **GnuPG**: A free implementation of the OpenPGP standard for encryption and signing data.
- **PHP-GnuPG**: A PHP extension that provides an interface to GnuPG functionality, allowing encryption, decryption, and key management in PHP scripts.
- **Requirements**: 
  - GnuPG must be installed on the system.
  - PHP must be installed with development tools (e.g., `php-dev` or `phpize`).
  - GPGME (GnuPG Made Easy) library is required for PHP-GnuPG.

This guide covers installation on **Linux (Ubuntu/Debian, CentOS/RHEL)**, **macOS**, and **Windows**.

---

## 1. Linux (Ubuntu/Debian-based Systems)

### Step 1: Install GnuPG
1. Update the package list:
   ```
   sudo apt update
   ```
2. Install GnuPG:
   ```
   sudo apt install gnupg
   ```
3. Verify installation:
   ```
   gpg --version
   ```

### Step 2: Install GPGME (Dependency for PHP-GnuPG)
1. Install the GPGME library and development files:
   ```
   sudo apt install libgpgme-dev
   ```

### Step 3: Install PHP and Development Tools
1. Install PHP and its development package (replace `8.1` with your PHP version):
   ```
   sudo apt install php8.1 php8.1-dev
   ```
2. Install PECL (PHP Extension Community Library):
   ```
   sudo apt install php-pear
   ```

### Step 4: Install PHP-GnuPG Extension
1. Install the extension via PECL:
   ```
   sudo pecl install gnupg
   ```
2. Enable the extension by adding it to `php.ini`:
   - Locate your `php.ini` file (e.g., `/etc/php/8.1/cli/php.ini` for CLI or `/etc/php/8.1/fpm/php.ini` for FPM).
   - Add the following line:
     ```
     extension=gnupg.so
     ```
3. Restart your web server (if applicable, e.g., Apache or PHP-FPM):
   ```
   sudo systemctl restart apache2
   # OR
   sudo systemctl restart php8.1-fpm
   ```
4. Verify the extension is loaded:
   ```
   php -m | grep gnupg
   ```

---

## 2. Linux (CentOS/RHEL-based Systems)

### Step 1: Install GnuPG
1. Enable the EPEL repository (if not already enabled):
   ```
   sudo yum install epel-release
   ```
2. Install GnuPG:
   ```
   sudo yum install gnupg
   ```
3. Verify installation:
   ```
   gpg --version
   ```

### Step 2: Install GPGME
1. Install GPGME and its development libraries:
   ```
   sudo yum install gpgme gpgme-devel
   ```

### Step 3: Install PHP and Development Tools
1. Install PHP and its development package (replace `8.1` with your PHP version):
   ```
   sudo yum install php php-devel
   ```
2. Install PECL:
   ```
   sudo yum install php-pear
   ```

### Step 4: Install PHP-GnuPG Extension
1. Install the extension via PECL:
   ```
   sudo pecl install gnupg
   ```
2. Enable the extension in `php.ini`:
   - Locate your `php.ini` file (e.g., `/etc/php.ini`).
   - Add the following line:
     ```
     extension=gnupg.so
     ```
3. Restart your web server (if applicable, e.g., Apache):
   ```
   sudo systemctl restart httpd
   ```
4. Verify the extension is loaded:
   ```
   php -m | grep gnupg
   ```

---

## 3. macOS

### Step 1: Install GnuPG
1. Install Homebrew (if not already installed):
   ```
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
   ```
2. Install GnuPG:
   ```
   brew install gnupg
   ```
3. Verify installation:
   ```
   gpg --version
   ```

### Step 2: Install GPGME
1. Install GPGME:
   ```
   brew install gpgme
   ```

### Step 3: Install PHP and Development Tools
1. Install PHP (replace `8.1` with your desired version):
   ```
   brew install php@8.1
   ```
2. Link PHP (if not already in PATH):
   ```
   brew link php@8.1
   ```
3. Install PECL (usually included with Homebrew PHP).

### Step 4: Install PHP-GnuPG Extension
1. Install the extension via PECL:
   ```
   pecl install gnupg
   ```
2. Enable the extension in `php.ini`:
   - Locate your `php.ini` file (e.g., `/usr/local/etc/php/8.1/php.ini`).
   - Add the following line:
     ```
     extension=gnupg.so
     ```
3. Restart the built-in PHP server or web server (if applicable):
   ```
   brew services restart php@8.1
   ```
4. Verify the extension is loaded:
   ```
   php -m | grep gnupg
   ```

---

## 4. Windows

### Step 1: Install GnuPG
1. Download GnuPG for Windows from the official site: [Gpg4win](https://www.gpg4win.org/).
2. Run the installer and follow the prompts to install GnuPG.
3. Verify installation by opening a Command Prompt and running:
   ```
   gpg --version
   ```

### Step 2: Install PHP
1. Download and install PHP from [php.net](https://www.php.net/downloads.php).
2. Extract the PHP files to a directory (e.g., `C:\php`).
3. Add PHP to your system PATH:
   - Right-click "This PC" > Properties > Advanced system settings > Environment Variables.
   - Edit the `Path` variable and add `C:\php`.

### Step 3: Install PHP-GnuPG Extension
**Note**: The PHP-GnuPG extension is not officially supported on Windows due to the lack of pre-built GPGME libraries compatible with PHP. However, you can attempt a manual setup:
1. Install a C compiler (e.g., MinGW or Visual Studio).
2. Download the GPGME source code from [gnupg.org](https://www.gnupg.org/download/).
3. Compile GPGME for Windows (this requires advanced knowledge and is not straightforward).
4. Download the PHP-GnuPG source from [PECL](https://pecl.php.net/package/gnupg).
5. Compile the extension using `phpize` and a compatible compiler (requires PHP development files).
6. Place the compiled `php_gnupg.dll` in the PHP extensions directory (e.g., `C:\php\ext`).
7. Enable the extension in `php.ini` (e.g., `C:\php\php.ini`):
   ```
   extension=php_gnupg.dll
   ```
8. Restart your web server (e.g., Apache) or test via CLI:
   ```
   php -m
   ```

**Alternative**: Use a pre-built DLL if available from a trusted source, or consider using a Linux-based environment (e.g., WSL2) for better support.

---

## Verification
After installation, create a test PHP script to confirm PHP-GnuPG is working:
```php
<?php
$gpg = new gnupg();
echo "PHP-GnuPG is installed and working!";
```
Run it via CLI:
```
php test.php
```
Or place it in your web server’s root directory and access it via a browser.

---

## Troubleshooting
- **GPGME not found**: Ensure `libgpgme-dev` (Linux) or `gpgme` (macOS) is installed.
- **Extension not loading**: Check `php.ini` path with `php --ini` and verify the extension line is correct.
- **Windows issues**: Consider using WSL2 (Windows Subsystem for Linux) with Ubuntu for a smoother experience.

---

This guide provides a general approach. Specific versions or configurations may require adjustments based on your system setup. For Windows, native support is limited, so a Linux-based environment is recommended for production use.