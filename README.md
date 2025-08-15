
```markdown
# 🔧 TikTok Clone - Laravel Backend API

Laravel REST API backend untuk TikTok Clone mobile application dengan konfigurasi hosting yang flexible.

## ✨ Features
- 🔐 **User Authentication** (Laravel Sanctum)
- 🎥 **Video Upload & Management**
- 👥 **Social Features** (likes, comments, follows)
- 📂 **Category Management**
- 🗄️ **Database Migrations & Seeders**
- 📁 **File Storage Management**
- 🌐 **CORS Support** untuk mobile app

## 🚀 Quick Setup

### Prerequisites
- PHP 8.1+
- Composer
- MySQL/PostgreSQL database
- Web server (Apache/Nginx)

### Installation
1. **Clone repository**
   ```bash
   git clone https://github.com/Driaaan17/tiktok-clone-backend.git
   cd tiktok-clone-backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   # Edit .env dengan database credentials Anda
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   php artisan storage:link
   ```

5. **Start server**
   ```bash
   php artisan serve
   # API akan jalan di http://localhost:8000
   ```

## ⚙️ Configuration

### Database (.env)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tiktok_clone
DB_USERNAME=root
DB_PASSWORD=your_password
```

### CORS untuk Flutter App
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5076
CORS_ALLOWED_ORIGINS=http://localhost:5076
```

## 🌐 API Endpoints

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/me` - Get current user

### Videos
- `GET /api/videos` - Get videos list
- `POST /api/videos` - Upload video
- `GET /api/videos/{id}` - Get video details
- `DELETE /api/videos/{id}` - Delete video
- `POST /api/videos/{id}/like` - Like/unlike video

### Users
- `GET /api/users/{id}` - Get user profile
- `POST /api/users/{id}/follow` - Follow/unfollow user
- `PUT /api/profile` - Update profile

### Categories
- `GET /api/categories` - Get categories list

## 🚀 Hosting Options

Lihat **[docs/hosting_guide.md](docs/hosting_guide.md)** untuk panduan deployment lengkap.

### Quick Hosting Checklist:
- [ ] Update `APP_URL` di .env
- [ ] Setup database di hosting
- [ ] Upload files Laravel (tanpa vendor/)
- [ ] Run `composer install` di server
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Update CORS untuk frontend domain

## 📱 Frontend Integration

**Mobile App Repository:** https://github.com/Driaaan17/tiktok-clone-mobile

### Untuk connect dengan Flutter:
1. Update URL di `lib/config/api_config.dart` (mobile repo)
2. Pastikan CORS sudah dikonfigurasi dengan benar
3. Test API endpoints dengan mobile app

## 🔧 Tech Stack
- **Laravel 11** - PHP web framework
- **MySQL** - Database
- **Laravel Sanctum** - API authentication
- **File Storage** - Local/cloud file storage
- **CORS** - Cross-origin resource sharing

## 🛡️ Security Features
- JWT Authentication via Sanctum
- CORS protection
- Input validation
- File upload security
- Rate limiting
- SQL injection protection

## 🤝 Contributing
1. Fork repository
2. Create feature branch
3. Make changes
4. Submit pull request

## 📄 License
MIT License

## 🆘 Support
- [Create Issue](https://github.com/Driaaan17/tiktok-clone-backend/issues) untuk bugs
- [Mobile App](https://github.com/Driaaan17/tiktok-clone-mobile) untuk frontend

---

**Made with ❤️ using Laravel**

🔗 **Frontend App**: https://github.com/Driaaan17/tiktok-clone-mobile
