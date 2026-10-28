**[Español](README.md)** · English

# Unread Tracker — GLPI plugin (glpi-unread)

GLPI plugin that adds an Odoo-style notification center and tracks **unread, updated and overdue tickets** with priority classification.

## Status

- Status: active (release 1.2.0).
- Version: see `VERSION` and `CHANGELOG.md`.
- Roadmap: `ROADMAP.md`.
- Operational documentation: `Obsidian/07.Desarrollos/26.glpi-unread/00.index.md`.

## Requirements

- GLPI 10.x or 11.x.
- PHP 7.4+ (GLPI 10.x) or 8.2+ (GLPI 11.x).
- MariaDB / MySQL (standard GLPI schema).

## Usage

Install and activate on a GLPI instance (see the Spanish README for the `/tmp` deployment rule):

```bash
sudo ./scripts/install.sh [--glpi-path /var/www/html/glpi] [--web-user www-data] [--admin-user glpi]
```

## Development

```bash
find . -name "*.php" -exec php -l {} \;
```

There is no automated test suite in the repository `[D]`.

## Documentation

- `README.md`: main documentation (Spanish): API, database schema and deployment.
- `README.en.md`: English summary.
- `SECURITY.md`: security policy.
- `CONTRIBUTING.md`: contribution rules.
- `ROADMAP.md`: project status and direction.
- `CHANGELOG.md`: change history.

## Security

Do not store secrets, tokens, `.env`, logs or local databases in the repository.

## License

AGPL-3.0-or-later. See [LICENSE](LICENSE).
