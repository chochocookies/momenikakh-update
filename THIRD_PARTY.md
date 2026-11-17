# Third-Party Licenses

**Last Updated:** 2024-12-23

This document provides an overview of third-party libraries, frameworks, fonts, and JavaScript packages used in this Laravel 13 application, along with their respective licenses.

---

## Maintenance

This section documents the process for maintaining the third-party dependency list.

### Adding New Dependencies

When adding a new dependency, follow these steps:

1. **Install the package** using the appropriate command:

   - For Composer (PHP packages):
     ```bash
     composer require vendor/package-name
     ```

   - For npm (JavaScript packages):
     ```bash
     npm install package-name
     ```

2. **Update THIRD_PARTY.md**:
   - Add the new package to the appropriate section (Composer Packages or npm Packages)
   - Include the package name, version, and license information
   - Add a brief note about usage if applicable
   - Update the **Last Updated** date at the top of the document to today's date (ISO 8601 format: YYYY-MM-DD)

3. **Commit changes** in the same commit as the dependency installation:
   ```bash
   git add composer.json composer.lock package.json package-lock.json THIRD_PARTY.md
   git commit -m "Add new dependency and update THIRD_PARTY.md"
   ```

**Example - Adding a new Composer package:**
```bash
# Install the package
composer require spatie/laravel-model-status

# Update THIRD_PARTY.md (add spatie/laravel-model-status to Composer Packages section)
# Update the Last Updated date

# Commit all changes
git add composer.json composer.lock THIRD_PARTY.md
git commit -m "Add spatie/laravel-model-status package"
```

### Removing Dependencies

When removing a dependency, follow these steps:

1. **Remove the package** using the appropriate command:

   - For Composer:
     ```bash
     composer remove vendor/package-name
     ```

   - For npm:
     ```bash
     npm uninstall package-name
     ```

2. **Update THIRD_PARTY.md**:
   - Remove the package entry from the appropriate section
   - Reorder packages alphabetically if needed for consistency
   - Update the **Last Updated** date at the top of the document to today's date (ISO 8601 format: YYYY-MM-DD)

3. **Commit changes** in the same commit as the dependency removal:
   ```bash
   git add composer.json composer.lock package.json package-lock.json THIRD_PARTY.md
   git commit -m "Remove dependency and update THIRD_PARTY.md"
   ```

**Example - Removing a Composer package:**
```bash
# Remove the package
composer remove vendor/package-name

# Update THIRD_PARTY.md (remove package entry)
# Update the Last Updated date

# Commit all changes
git add composer.json composer.lock THIRD_PARTY.md
git commit -m "Remove vendor/package-name"
```

### Updating Last-Updated Date

- **Always update the Last Updated date** when making any changes to the dependency list (adding, removing, or modifying entries)
- Use ISO 8601 format: `YYYY-MM-DD` (e.g., `2024-12-23`)
- Update the date at the top of the document, below the main heading

### Future Automation

Consider implementing a pre-commit hook to automatically update the Last Updated date when changes are made to dependency files. This could be implemented using a simple script that:

1. Detects changes to `composer.lock` or `package-lock.json`
2. Updates the Last Updated date in `THIRD_PARTY.md`
3. Automatically stages the updated file

This would ensure the date is always current without manual intervention.

---

## GSAP (GreenSock Animation Platform)

- **License Type:** Commercial/Free Dual License
- **Commercial Usage:** Free for most projects; commercial license required for certain types of commercial applications
- **Reference URL:** [https://greensock.com/](https://greensock.com/)
- **Status:** Using free features only

GSAP is provided under a "Free For Standard Use" license for most websites and applications. A commercial license is required for:

- Products, services, or applications that charge fees for use (subscription, per-use, or one-time fee)
- Apps, games, or tools that are sold or monetized in any way
- Internal tools used by for-profit organizations (some exceptions apply)

See [GSAP License Details](https://greensock.com/why-license) for full information.

---

## Fonts

### Cormorant Garamond

- **License:** SIL Open Font License (OFL) 1.1
- **Source:** Google Fonts
- **Usage:** Web fonts for headings and titles

### Plus Jakarta Sans

- **License:** SIL Open Font License (OFL) 1.1
- **Source:** Google Fonts
- **Usage:** Web fonts for body text and UI elements

Both fonts are licensed under the [SIL Open Font License 1.1](https://scripts.sil.org/OFL).

---

## Composer Packages

| Package | Version | License |
|---------|---------|---------|
| fakerphp/faker | ^1.23 | MIT |
| larastan/larastan | ^3.0 | MIT |
| laravel/framework | ^13.0 | MIT |
| laravel/fortify | ^1.42 | MIT |
| laravel/pail | ^1.2.2 | MIT |
| laravel/pint | ^1.24 | MIT |
| laravel/sail | ^1.41 | MIT |
| laravel/tinker | ^3.0 | MIT |
| livewire/livewire | ^4.4 | MIT |
| mockery/mockery | ^1.6 | BSD-3-Clause |
| nunomaduro/collision | ^8.6 | MIT |
| pestphp/pest | ^4.0 | MIT |
| pestphp/pest-plugin-laravel | ^4.0 | MIT |
| predis/predis | ^3.7 | MIT |
| sentry/sentry-laravel | * | MIT |
| phpunit/phpunit | ^12.5 | BSD-3-Clause |

---

## npm Packages

| Package | Version | License |
|---------|---------|---------|
| @tailwindcss/vite | ^4.0.0 | MIT |
| axios | ^1.11.0 | MIT |
| concurrently | ^9.0.1 | MIT |
| laravel-vite-plugin | ^2.0.0 | MIT |
| tailwindcss | ^4.0.0 | MIT |
| vite | ^7.0.7 | MIT |

---

## Notes

- This document should be updated whenever new third-party packages are added
- Version numbers should be updated when packages are upgraded
- License information should be verified for each package
- Refer to individual package repositories for detailed licensing information
- See the Maintenance section above for detailed procedures
