![Build Status](https://travis-ci.org/TaysirTayyab/phpunit-parallel-runner.svg?branch=phpunit4) ![Code Climate](https://codeclimate.com/github/TaysirTayyab/phpunit-parallel-runner/badges/gpa.svg) ![Test Coverage](https://codeclimate.com/github/TaysirTayyab/phpunit-parallel-runner/badges/coverage.svg)

# PHPUnit Parallel (Node) Runner
A parallelizer for running PHPUnit on multiple nodes. While many plugins exist to run PHPUnit in parallel
_processes_, this extensions allows for running PHPUnit in parallel on multiple _nodes_. This is often required
 in various CI services such as CircleCI and TravisCI.
 
## Dependencies
This project is an extension for **PHPUnit Version 4**.

The development environment for this project is configured using docker, removing the need to actually install
anything on your maching. Simply install the [Docker Engine](https://docs.docker.com/engine/installation/) and
use the scripts in `bin/`.

## Usage
An entrypoint script is packaged with the extension (entitle `phpunit-parallel.php`). This can be run with the
arguments `--current-node` and `--total-nodes` representing the index of the node in the cluster (numbered 0-n)
and the total number of nodes in the cluster (n), respectively.

The `--current-node` option expects a 0-indexed value. That is for a cluster with 3 nodes, the node indexes would
be 0, 1, and 2.

### Example Usage
```
> phpunit-parallel.php --current-node=0 --total-nodes=1 tests/
> phpunit-parallel.php --current-node=2 --total-nodes=5 tests/
> phpunit-parallel.php --current-node=1 --total-nodes=2 --configuration phpunit.xml
```

## Development Setup

1. Clone the repository
2. In the project root run composer
```
> bin/composer install
```
3. Run the unit tests packaged with this extension
```
> bin/phpunit
```
