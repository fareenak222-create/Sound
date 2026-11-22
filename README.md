# SOUND — Music & Video Entertainment Website

Dark, responsive, database-driven music and video site with user accounts, ratings/reviews and a full admin panel.
**Stack:** PHP 8+, MySQL/MariaDB (PDO prepared statements), Apache (XAMPP), HTML5, CSS3, JavaScript, Bootstrap 5.

## 1. Install on XAMPP (Windows / macOS / Linux)
1. Install XAMPP with **PHP 8.1+**, start **Apache** and **MySQL** in the control panel.
2. Copy this folder to `htdocs/` so you have `htdocs/sound/index.php`.
3. Open **http://localhost/phpmyadmin** → *Import* → choose `database/sound_db.sql` → *Go*.
   (This creates the database `sound_db`, all tables and clearly marked SAMPLE data. Re-importing resets everything.)
   Or from a terminal: `mysql -u root < database/sound_db.sql`
4. Check `includes/config.php` (defaults: host `localhost`, user `root`, empty password, database `sound_db`).
   On any real server use a dedicated MySQL user with a strong password.
5. Browse to **http://localhost/sound/**.
6. `uploads/` (and its sub-folders) must be writable by Apache (automatic on XAMPP for Windows; on Linux/macOS: `chmod -R 775 uploads`).
7. Uploading large videos: raise `upload_max_filesize` and `post_max_size` in `xampp/php/php.ini` (e.g. `256M`), then restart Apache. The app's own limits are in `includes/config.php` (image 3 MB, audio 25 MB, video 200 MB).

Apache `.htaccess` files are included (blocks directory listing, blocks `includes/` and `database/`, and stops PHP executing inside `uploads/`). Make sure `AllowOverride All` is enabled (XAMPP default is fine).

## 2. Create a secure administrator account
The SQL file ships a **demo** admin so you can test straight away:

| Role | User ID | Password |
|---|---|---|
| Demo admin | `admin_demo` | `Admin@12345` |
| Demo user | `listener1` | `User@12345` |

**Before going live, create your own admin and delete the demo accounts:**
```
cd htdocs/sound
php create_admin.php        # (XAMPP: C:\xampp\php\php.exe create_admin.php)
```
The script asks for User ID, name, email, phone, address and a password (hidden input, stored only as a bcrypt hash).
Then log in, open **Admin → Users**, and delete `admin_demo` / `listener1`. (Sample data can be removed in the admin pages too.)
`create_admin.php` refuses to run from a browser. Admins can also promote/demote users in **Admin → Users**; the last active admin cannot be removed or suspended.

## 3. What's included
**Public:** Home (hero, search, latest 5 songs/videos with NEW badges, featured artists, genres) · Music & Videos (filters, sorting, pagination) · detail pages (info, real audio/video player only when a file exists, related items, ratings, reviews) · Browse by artists/albums/genres/years/languages · Search (title, artist, album, year, genre, language) · About · Contact (stored in DB, viewed in admin) · Register / Login / Logout / Profile (edit details, change password, my reviews).

**Admin (`/admin/`)**: dashboard statistics (counts, average rating, rating distribution, top rated) · add/edit/delete music & videos with cover/thumbnail/audio/video upload · manage artists, albums, genres, years, languages · manage users (suspend/activate, role, delete) · moderate reviews (hide/show/delete) · contact messages · site settings (name, tagline, about, contact info, NEW-badge period, items per page).

**Database (`sound_db`)**: users, artists, albums, genres, languages, years, music, videos, ratings, reviews, contact_messages, settings — with primary/foreign keys, unique constraints, indexes, CHECK constraints (rating 1–5) and delete rules (RESTRICT for catalogue references, SET NULL for album, CASCADE for user/item → ratings/reviews).

## 4. Security measures
PDO prepared statements everywhere · `password_hash`/`password_verify` (bcrypt) · session cookie flags (HttpOnly, SameSite) and session ID regeneration at login · CSRF tokens on every POST (including logout and all admin actions) · output escaping on all dynamic output · role checks on every admin page (403 for non-admins) · users can only change their own profile/rating/review (identity always taken from the session) · login throttle (5 failures → 60 s lock) · suspended accounts are logged out immediately · upload validation by real MIME type (finfo), extension whitelist, size limit, random file names, PHP execution disabled in `uploads/` · confirmation prompts before deletions · contact form honeypot + rate limit · no credentials or hashes are ever displayed.

## 5. Testing performed (and what was NOT tested)
Automated end-to-end test of **87 checks** against PHP 8.3 + MariaDB 10.11 (PHP built-in server): all public pages; search, filters, sorting, pagination; SQL-injection and XSS probes; registration validation (mismatch, duplicate ID/email, bad CSRF); login/logout; access control (anonymous and normal user vs admin pages); rate/review create/update/validate/delete; profile edit and password checks; contact form; admin add/edit/delete music with real image + audio upload; rejection of fake images/audio containing PHP; file cleanup on delete; artist/album/genre/year CRUD incl. duplicate and in-use deletion rules; suspend user (immediate logout), self-delete guard; review moderation; settings validation and effect (items per page, NEW period).

**Not tested / limitations — please verify on your machine:** real Apache/XAMPP with `.htaccess` rules (tested on the PHP built-in server, so the `.htaccess` deny rules were not exercised); MySQL itself (tested on MariaDB, which is compatible); layouts in real browsers/devices (CSS is mobile-first Bootstrap 5, but I could not take screenshots in this environment); very large video uploads (depends on your php.ini); sending e-mail (contact messages are stored in the database and shown in Admin → Messages instead). Bootstrap, Bootstrap Icons and the Outfit font load from CDNs, so the browser needs internet access.

Sample media: three short generated tones are included for `Neon Skies`, `Midnight Static` and `Raga Reboot` so the audio player can be tried; all other titles show "No audio/video file available" until you upload one in the admin panel. Covers fall back to generated colour artwork when no image is uploaded.

## 6. Folder structure
```
sound/
├── index.php music.php videos.php item.php search.php browse.php about.php contact.php
├── register.php login.php logout.php profile.php create_admin.php
├── actions/review.php            (rate / review / delete)
├── admin/                        (dashboard, music, videos, taxonomy, users, reviews, messages, settings)
├── includes/                     (config, bootstrap/helpers, header, footer, listing) – web access blocked
├── assets/css/style.css  assets/js/app.js
├── uploads/{covers,audio,video,artists}
└── database/sound_db.sql
```
