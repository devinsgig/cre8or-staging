# ShaunSocial

The Ultimate PHP Social Network Platform

## Version
1.4

## Documentation
[https://docs.shaunsocial.com](https://docs.shaunsocial.com)

## Support
📧 support@shaunsocial.com

---

## Local Development Notes
- Environment variables go in `.env` (not committed).
- Sample config is in `.env.example`.
- Run `composer install` and `npm install` after cloning.
- Run `php artisan key:generate` if `APP_KEY` is missing.
- Use `php artisan migrate` to set up the database.

## Deployment Notes
- Dependencies (`vendor/`, `node_modules/`) are not included in Git.
- Server should run `composer install --no-dev` and `npm ci && npm run build` on deploy.
- Storage directories contain `.gitkeep` files so Git tracks empty folders.
