# Testing

## Building the test environment

The test environment is containerized with Docker.
You need to retrieve the test framework and generate the classmap before running the test suites.

This is done by running the following command:
```
docker-compose composer install
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

## Troubleshooting

If the test suites complain about missing classes, it's probably a symptom of an outdated classmap.
To resolve that issue, run the following command:
```
docker-compose composer dump-autoload
```
