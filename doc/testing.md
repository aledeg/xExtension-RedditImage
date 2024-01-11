# Testing

## Building the test environment

The test environment is containerized with Docker.
You need to retrieve the test framework and generate the classmap before running the test suites.

This is done by running the following command:
```
docker-compose run composer install
```

## Running the test suites

There is two test suites in this project.
The first test suite validates the internal features of this extension.
This is the default test suite.
The second test suite validates that external API behaviors are unchanged.
This is the canary test suite.

To run the default test suite, use the following command:
```
docker-compose run phpunit
```

To run the canary test suite, use the following command:
```
docker-compose run phpunit --testsuite canary
```

**NOTE** Prior to run any test suite, copy the content of the _phpunit.xml.dist_ file into the _phpunit.xml_ file.
Then modify the _phpunit.xml_ file content by defining the constants _IMGUR_CLIENT_ID_ and _FLICKR_API_KEY_. If this is not done, the test suites will skip some tests and ask for those values.

## Running the static analysis

To ensure code quality, the code follows standards enforced through static analysis.
The analyser in use is phpstan, configured to run on level 7.

To run the static analysis, use the following command:
```
docker-compose run phpstan analyse --memory-limit 512M
```

## Running the coding standard fixer

To ensure cohesive coding standard, the code can be analyzed and fixed by using php-cs-fixer.

To run the coding standard fixer, use the following command:
```
docker-compose run phpcsfixer fix --allow-risky yes
```

## Troubleshooting

If the test suites or the static analysis complain about missing classes, it's probably a symptom of an outdated classmap.
To resolve that issue, run the following command:
```
docker-compose run composer dump-autoload
```
