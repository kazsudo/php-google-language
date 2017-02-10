## About

- Analyze text using [Natural Language API](https://cloud.google.com/natural-language/) and save response to [Cloud Datastore](https://cloud.google.com/datastore/).
- Visualize syntax structure as tree diagram

## Requirement

- composer `>=1.0.0`
- PHP `>=5.6`

## Installation

Install packages via [Composer](https://getcomposer.org/).
```
composer install
```

## Configuration

Copy `app/config.php.dist` as `app/config.php`.
Specity *Service account key JSON path*.
```php
  "google_cloud" => [
    "key_file_path" => "<Service account key JSON path>",
  ],
```

## Usage

### Analyze

Analyze local text file.
```
php language.php analyze text.txt
```
Analyze text file on [Cloud Storage](https://cloud.google.com/storage/).
```
php language.php analyze gs://your-bucket/text.txt
```

#### Options

Use `--name` to change the name used for visualize.

```
php language.php analyze --name <name> text.txt
```

Use ` --config` to specify a configuration file other than `app/config.php`,

```
php language.php analyze --config <Configuration file path> text.txt
```

### Visualize

Move to `web/` and start the development server.

```
cd web/
php -S localhost:8000
```

Access the viewer.

```
http://localhost:8000/view.php
```

Input text file name and press `View` button.

```
http://localhost:8000/view.php?name=text.txt
```
