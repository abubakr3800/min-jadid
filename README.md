# Knowledge Hub - Interactive Content Platform

A modern, interactive knowledge sharing platform built with PHP and Firebase, featuring real-time data, user-generated content, and social interactions.

## 🚀 Features

### Core Functionality
- **User Authentication**: Secure registration and login with Firebase Auth
- **Real-time Data**: Live updates for views, ratings, likes, and comments
- **Content Creation**: Rich article creation with categories and tags
- **Interactive Elements**: Rating system, likes, comments, and view tracking
- **User Profiles**: Personal profiles with bio, LinkedIn integration, and post history
- **Category Filtering**: Browse content by categories
- **Responsive Design**: Modern, mobile-friendly interface

### Real-time Interactions
- **View Tracking**: Automatic view counting when posts are accessed
- **Rating System**: 1-5 star rating with real-time updates
- **Like System**: Like/unlike posts with instant counter updates
- **Comments**: Real-time commenting system
- **User Analytics**: Track user engagement and post performance

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: Firebase Realtime Database
- **Authentication**: Firebase Authentication
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Custom CSS with Font Awesome icons
- **Dependencies**: Composer for PHP package management

## 📋 Prerequisites

- PHP 7.4 or higher
- Composer
- XAMPP, WAMP, or similar local server
- Firebase account
- Modern web browser

## 🚀 Quick Start

### 1. Clone or Download
```bash
git clone <repository-url>
cd knowledge-hub
```

### 2. Run Installation Script
Navigate to `install.php` in your browser to run the automated setup:
```
http://localhost/your-project/install.php
```

### 3. Manual Setup (Alternative)

#### Install Dependencies
```bash
composer install
```

#### Firebase Configuration
1. Create a Firebase project at [Firebase Console](https://console.firebase.google.com/)
2. Enable Authentication (Email/Password)
3. Create Realtime Database
4. Download service account key as `config/firebase-credentials.json`
5. Update `config/firebase-config.php` with your project details

#### Database Rules
Set these rules in your Firebase Realtime Database:
```json
{
  "rules": {
    "users": {
      "$uid": {
        ".read": "$uid === auth.uid",
        ".write": "$uid === auth.uid"
      }
    },
    "posts": {
      ".read": true,
      ".write": "auth != null"
    },
    "post_ratings": {
      "$postId": {
        ".read": true,
        ".write": "auth != null"
      }
    },
    "post_likes": {
      "$postId": {
        ".read": true,
        ".write": "auth != null"
      }
    }
  }
}
```

### 4. Start Your Server
- Start XAMPP/WAMP
- Navigate to your project folder
- Access via `http://localhost/your-project/`

## 📁 Project Structure

```
knowledge-hub/
├── api/                    # API endpoints
│   ├── auth.php           # Authentication API
│   └── posts.php          # Posts API
├── config/                 # Configuration files
│   ├── firebase-config.php # Firebase configuration
│   └── firebase-credentials.json # Service account key
├── css/                    # Stylesheets
│   ├── all.css            # Main styles
│   ├── components.css     # Component styles
│   ├── layout.css         # Layout styles
│   └── responsive.css     # Responsive design
├── js/                     # JavaScript files
│   ├── api.js             # API client
│   ├── auth.js            # Authentication
│   ├── profile.js         # Profile management
│   ├── quotes.js          # Content management
│   └── ui.js              # UI interactions
├── pages/                  # HTML pages
│   ├── login.html         # Login page
│   ├── signup.html        # Registration page
│   ├── profile.html       # User profile
│   └── new-post.html      # Post creation
├── img/                    # Images and assets
├── index.php              # Main homepage
├── post.php               # Individual post view
├── new-post.php           # Post creation page
├── profile.php            # User profile page
├── composer.json          # PHP dependencies
├── install.php            # Installation script
├── FIREBASE_SETUP.md      # Firebase setup guide
└── README.md              # This file
```

## 🔧 Configuration

### Firebase Setup
1. **Project Creation**: Create a new Firebase project
2. **Authentication**: Enable Email/Password authentication
3. **Database**: Set up Realtime Database
4. **Service Account**: Generate and download credentials
5. **Rules**: Configure database security rules

### Environment Variables
For production, consider using environment variables for sensitive data:
```php
// In firebase-config.php
$databaseUri = getenv('FIREBASE_DATABASE_URI') ?: 'https://your-project.firebaseio.com';
```

## 🎯 Features in Detail

### User Authentication
- Secure registration with email validation
- Password strength requirements
- Session management
- Profile creation on registration

### Content Management
- Rich text article creation
- Category and tag system
- Draft saving and preview
- Content validation

### Real-time Interactions
- **Views**: Automatic tracking when posts are accessed
- **Ratings**: 1-5 star system with average calculation
- **Likes**: Toggle like/unlike with real-time updates
- **Comments**: Threaded commenting system

### User Profiles
- Personal information and bio
- LinkedIn profile integration
- Post history and statistics
- Engagement metrics

### Search and Filtering
- Category-based filtering
- Tag-based search
- User-friendly navigation

## 🔒 Security Features

- Firebase Authentication for secure user management
- Input validation and sanitization
- XSS protection
- CSRF protection
- Secure database rules
- File upload restrictions

## 📱 Responsive Design

- Mobile-first approach
- Responsive grid system
- Touch-friendly interactions
- Optimized for all screen sizes

## 🚀 Deployment

### Local Development
1. Use XAMPP/WAMP for local development
2. Configure virtual hosts if needed
3. Enable error reporting for debugging

### Production Deployment
1. Set up a production server (Apache/Nginx)
2. Configure SSL certificates
3. Set up proper Firebase security rules
4. Enable error logging
5. Optimize performance

## 🐛 Troubleshooting

### Common Issues

1. **"API is not defined" error**
   - Check script loading order
   - Verify all JavaScript files are accessible

2. **Firebase connection errors**
   - Verify credentials file path
   - Check Firebase project configuration
   - Ensure database rules allow operations

3. **CORS errors**
   - Configure Firebase project settings
   - Check domain whitelist

4. **Authentication issues**
   - Verify Email/Password auth is enabled
   - Check user registration process

### Debug Mode
Enable debug mode by adding to PHP files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📈 Performance Optimization

- Use Firebase caching strategies
- Implement lazy loading for images
- Optimize database queries
- Minify CSS and JavaScript
- Enable GZIP compression

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
1. Check the troubleshooting section
2. Review Firebase documentation
3. Check browser console for errors
4. Verify all dependencies are installed

## 🔄 Updates

To update the project:
1. Backup your configuration files
2. Pull latest changes
3. Run `composer update`
4. Test all functionality
5. Update Firebase rules if needed

---

**Built with ❤️ for the knowledge sharing community** 