# Contributing

Contributions are welcome. This project accepts pull requests on [GitHub][].

## Communication Channels

You can find help and discussion in the following places:

* GitHub Issues: <https://github.com/manuelkiessling/php-ai-tool-bridge/issues>

## Reporting Bugs

Report bugs using the project's [issue tracker][issues].

⚠️ _**ATTENTION!!!** DO NOT include passwords or other sensitive information in
your bug report._

When submitting a bug report, please include enough information to reproduce the
bug. A good bug report includes the following sections:

* **Description**

  Provide a short and clear description of the bug.

* **Steps to reproduce**

  Provide steps to reproduce the behavior you are experiencing. Please try to
  keep this as short as possible. If able, create a reproducible script outside
  of any framework you are using. This will help us to quickly debug the issue.

* **Expected behavior**

  Provide a short and clear description of what you expect to happen.

* **Screenshots or output**

  If applicable, add screenshots or program output to help explain your problem.

* **Environment details**

  Provide details about the system where you're using this package, such as PHP
  version and operating system.

* **Additional context**

  Provide any additional context that may help us debug the problem.

## Fixing Bugs

This project welcomes pull requests to fix bugs!

If you see a bug report that you'd like to fix, please feel free to do so.
Following the directions and guidelines described in the "Adding New Features"
section below, you may create bugfix branches and send pull requests.

## Adding New Features

If you have an idea for a new feature, it's a good idea to check out the
[issues][] or active [pull requests][] first to see if anyone is already working
on the feature. If not, feel free to submit an issue first, asking whether the
feature is beneficial to the project. This will save you from doing a lot of
development work only to have your feature rejected. We don't enjoy rejecting
your hard work, but some features don't fit with the goals of the project.

When you do begin working on your feature, here are some guidelines to consider:

* Your pull request description should clearly detail the changes you have made.
  We will use this description to update the CHANGELOG. If there is no
  description, or it does not adequately describe your feature, we may ask you
  to update the description.
* Please **write tests** for any new features you add.
* Please **ensure that tests pass** before submitting your pull request.
  manuelkiessling/php-ai-tool-bridge automatically runs tests for pull requests. However,
  running the tests locally will help save time. _Hint: run `php vendor/bin/phpunit tests/`._
* **Use topic/feature branches.** Please do not ask to pull from your main branch.
  * For more information, see "[Understanding the GitHub flow][gh-flow]."
* **Submit one feature per pull request.** If you have multiple features you
  wish to submit, please break them into separate pull requests.

## Developing

To develop this project, you will need [PHP](https://www.php.net) 8.2 or greater
and [Composer](https://getcomposer.org).

After cloning this repository locally, execute the following commands:

``` bash
cd /path/to/repository
composer install
```

Now, you are ready to develop!

### Project Structure

This project uses [pds/skeleton](https://github.com/php-pds/skeleton) as its
base folder structure and layout.

### Running Tests

The following must pass before we will accept a pull request. If this does not
pass, it will result in a complete build failure. Before you can run this, be
sure to `composer install`.

To run all the tests and coding standards checks, execute the following from the
command line, while in the project root directory:

```
composer test
```

CaptainHook will automatically run all tests before pushing to the remote
repository.

[github]: https://github.com/manuelkiessling/php-ai-tool-bridge
[issues]: https://github.com/manuelkiessling/php-ai-tool-bridge/issues
[pull requests]: https://github.com/manuelkiessling/php-ai-tool-bridge/pulls
[psr-12]: https://www.php-fig.org/psr/psr-12/
[gh-flow]: https://guides.github.com/introduction/flow/
[conventional commits]: https://www.conventionalcommits.org/
