# AI Lifestyle

Share your AI-age lifestyle, connecting with people, find meaning and get inspired.

Made by AI for humans

## Features

- User authentication (register, login, Google login)
- Widget system for sharing lifestyle content (text, images, videos, weblinks)
- Following system to connect with users
- Like and comment functionality for widgets
- Full-text search for content
- Tag-based categorization
- Responsive design for desktop and mobile
- Error handling for server, AJAX, and JavaScript errors

## Database Setup

### Prerequisites

- MySQL server (version 5.7 or higher)
- PHP 7.4 or higher
- Composer for dependency management

### Setup Instructions

1. **Create the database**

   The application uses a MySQL database. You can create it manually or use the provided `setup.sql` script:

   ```bash
   mysql -u root -p < setup.sql
   ```

   This will create the `ai_lifestyle` database and all necessary tables with default data.

   **Charset and collation**: see SQL ()`utf8mb4` charset and `utf8mb4_general_ci`)

2. **Configure database connection**

   Database credentials are stored in the `config.yml` file in the root directory. Update this file with your database connection details:

   ```yml
   database:
     host:     localhost     # Your database host
     dbname:   ai_lifestyle  # Your database name
     username: root          # Your database username
     password: ""            # Your database password
   ```

3. **Install dependencies**

   Run the following command in the project root directory to install all required dependencies:

   ```bash
   composer install
   ```

4. **Demo User Account**

   The `setup.sql` script creates a demo user account for development purposes:
   
   ```
   Username: demo
   Email: demo@example.com
   Password: demo123
   ```
   
   This account can be used for testing and development without needing to create a new user.

### Database Structure

The database includes the following tables:

- **users**: Stores user account information
- **widgets**: Contains the main content shared by users
- **tags**: Predefined and user-created tags for categorizing content
- **widget_tags**: Relationship table connecting widgets to tags
- **likes**: Records which users have liked which widgets
- **comments**: Stores user comments on widgets
- **follows**: Tracks user follow relationships

## Directory Structure

```
/
├── assets/              # Assets (images)
├── classes/             # PHP classes (models, utilities)
│   ├── Config/          # Configuration classes
│   └── Utils/           # Utility classes
├── pages/               # Application pages
│   ├── home/            # Home page
│   ├── login/           # Login page
│   ├── profile/         # Profile page
│   └── ...              # Other pages
├── shared/              # Shared assets
│   └── style.css        # Global styles
├── uploads/             # User uploaded files
├── vendor/              # Composer dependencies
├── ajax.php             # AJAX request handler
├── composer.json        # Composer configuration
├── config.yml           # Application configuration
├── controller.js        # Main js controller
├── error-handler.js     # Error handler
└── index.php            # Main entry point

    setup.sql            # Database setup script
```

## License

Copyright (C) Walter A. Jablonowski 2025, free under [MIT license](LICENSE)

This app is build upon PHP and free software (see [credits](credits.md)).

[Privacy](https://walter-a-jablonowski.github.io/privacy.html) | [Legal](https://walter-a-jablonowski.github.io/imprint.html)
