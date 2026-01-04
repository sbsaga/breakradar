# BreakRadar

**Breaking Change Radar for PHP** – detect silent breaking changes in your code, APIs, and public methods before they hit production.

![BreakRadar](https://img.shields.io/badge/BreakRadar-v1.0-blue?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.1+-8892BF?style=for-the-badge)
![License](https://img.shields.io/badge/License-MIT-lightgrey?style=for-the-badge)

---

## 🚀 Overview

BreakRadar is a **developer-focused CLI tool** that automatically detects breaking changes between your base branch (usually `main`) and your feature/PR branch. It works **even if all tests pass**.

> **Problem it solves:** developers accidentally remove methods, change signatures, or modify public APIs, breaking consumers silently. Normal CI/testing pipelines do **not** catch this.

BreakRadar answers the question:

> _"Did this PR break someone else’s code?"_

---

## 💡 Key Features

- ✅ Detect **removed public methods**
- ✅ Detect **changed method signatures**
- ✅ Compare **base branch vs PR branch automatically**
- ✅ Fully **CI-compatible** (GitHub Actions)
- ✅ Git-robust: works with main, master, or any default branch
- ✅ Production-ready CLI
- ✅ JSON + human-readable output
- ✅ Extensible for API field diff, enums, configs in the future

---

## 🎯 Use Cases

1. **Public method removal**
   ```php
   public function legacy(): void {}
   ```
   If removed in a PR → BreakRadar fails CI

2. **Method signature change**
   ```php
   // Before
   public function create(string $name): void {}
   // After
   public function create(string $name, bool $force): void {}
   ```
   Detected and flagged

3. **API or service integration**
   - Any internal or external consumer using your PHP classes  
   - Prevents silent runtime bugs before deployment

4. **Multi-branch / PR pipelines**
   - Detect breaking changes automatically on GitHub Actions
   - Fail PRs before merging

---

## 🛠 Installation

**Composer:**

```bash
composer require sbsaga/breakradar --dev
```

**Optional:** global install

```bash
composer global require sbsaga/breakradar
```

---

## ⚡ Usage

### Local CLI

```bash
php bin/breakradar check
```

- Will snapshot **base branch** (`origin/main` or `origin/master`)
- Will snapshot **current branch**
- Will compare and report breaking changes
- Exit code = `1` if breaking changes found (CI-friendly)

---

### GitHub Actions

Create `.github/workflows/breakradar.yml`:

```yaml
name: BreakRadar

on:
  pull_request:
    branches: [ main ]
  push:
    branches: [ main ]

jobs:
  check:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - uses: shivammathur/setup-php@v3
        with:
          php-version: '8.2'
          extensions: mbstring, json

      - run: composer install --no-interaction --prefer-dist

      - run: php bin/breakradar check
```

---

## 🧱 Project Structure

```
breakradar/
├── bin/
│   └── breakradar          # CLI entry point
├── src/
│   ├── Command/            # Symfony Console command
│   ├── Analyzer/           # Git + Public API snapshot
│   ├── Diff/               # Breaking change diff engine
│   ├── Reporter/           # Human-readable reporting
│   └── Config/             # Config (future use)
├── action/
│   └── action.yml          # GitHub Action config
├── tests/                  # PHPUnit tests
├── composer.json
└── README.md
```

---

## 🧪 Example Workflow

### Add a breaking change

```php
public function legacy(): void {}
```

Run:

```bash
php bin/breakradar check
```

Output:

```
No breaking changes detected.
```

### Remove the method

```php
// Deleted legacy()
```

Run again:

```
Breaking changes detected:
 - Public method removed: App\SomeClass::legacy
```

Exit code: `1` → CI fails

---

## 📈 Why BreakRadar is Useful

- Prevents **silent production bugs**
- Forces **better API discipline**
- Saves **debugging time**
- Stack-agnostic → works for **any PHP backend**
- Especially valuable for **microservices**, libraries, and shared packages

---

## ⚙️ Configuration

- Currently zero-config; snapshots stored in `.breakradar/`  
- Future configs possible:
  - Ignore methods/classes  
  - Detect API JSON field changes  
  - Enum/constant validation  

---

## 🏆 Roadmap (v2+)

- API response field diff
- Config / ENV shape checks
- Event / Queue payload checks
- JSON artifact output for CI dashboards
- Automatic PR comments with detailed report

---

## 📝 License

MIT License – feel free to fork, extend, or use in commercial projects.

---

## 👨‍💻 Author

**sbsaga** – PHP backend developer  
[GitHub](https://github.com/sbsaga)

---

## 📦 Download

```bash
git clone https://github.com/sbsaga/breakradar.git
cd breakradar
composer install
php bin/breakradar check
```

**BreakRadar:** Stop silent breaking changes before they hit production. 🚨
