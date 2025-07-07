# Smiles Project Planner

A modern, secure, and responsive PHP web application for uploading project documents and meeting recordings, running AI-powered workflows, and visualizing results in a Kanban board.

---

## Features

- **User Authentication:** Secure login system with environment-based credentials.
- **Modern UI:** Responsive design with a professional header, footer, and loader overlay.
- **Stepper Workflow:** Guided, multi-step process for uploading documents and audio.
- **AI Integration:** Runs an external workflow and displays results as a Kanban board.
- **Drag & Drop Kanban:** Move tasks between columns interactively.
- **Session Management:** Secure session handling and logout.
- **File Management:** Uploaded files and API responses are stored for reference.
- **Mobile Friendly:** Fully responsive for desktop, tablet, and mobile.

---

## Installation & Setup

### 1. **Requirements**

- PHP 7.4 or higher
- Web server (Apache, Nginx, XAMPP, etc.)
- Composer (optional, if you want to use PHP dotenv or other libraries)

### 2. **Clone or Download the Repository**

```sh
git clone <your-repo-url>
cd <project-directory>
```

### 3. **Directory Structure**

```
/
|-- index.php         # Login page
|-- upload.php        # Main application (protected)
|-- header.php        # Shared header
|-- footer.php        # Shared footer
|-- logout.php        # Logout handler
|-- style.css         # All application styles
|-- upload/           # Uploaded files, logo, and API responses
    |-- logo.png      # Logo used in header
    |-- ...           # Uploaded documents, audio, and response files
```

### 4. **Environment Variables**

Create a `.env` file in the project root (optional, for local development):

```
USERNAME=yourusername
PASSWORD=yourpassword
```

**Or** set environment variables in your web server configuration:

- **Apache (.htaccess):**
  ```
  SetEnv USERNAME yourusername
  SetEnv PASSWORD yourpassword
  ```
- **Nginx/php-fpm:**  
  ```
  env[USERNAME] = yourusername
  env[PASSWORD] = yourpassword
  ```

**Note:**  
If using a `.env` file, ensure the following code is at the top of `index.php` (before `getenv` is called):

```php
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
}
```

---

## Running the Application

1. **Start your web server** (e.g., XAMPP, MAMP, or built-in PHP server):

   ```sh
   php -S localhost:8000
   ```

2. **Open your browser and go to:**

   ```
   http://localhost:8000/index.php
   ```

3. **Login** using the credentials set in your environment.

4. **Use the stepper** to upload documents and audio, run the workflow, and interact with the Kanban board.

---

## Customization

- **Logo:** Replace `upload/logo.png` with your own logo.
- **Branding:** Edit `header.php` and `footer.php` for your organization.
- **Styling:** All styles are in `style.css` for easy customization.

---

## Security Notes

- All uploads and API responses are stored in the `upload/` directory.
- Sessions are used for authentication and workflow state.
- Always use strong credentials and secure your server in production.

---

## License

MIT License (or your preferred license)

---

If you need further customization or have questions, feel free to ask! 