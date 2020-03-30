#!/usr/bin/env bash

export DOCKER_REGISTRY_HOST=registry.hub.docker.com

export SED_ACCOUNT=library
export SED_REPO=alpine
export SED_TAG=3.11.3
export SED_IMAGE=${DOCKER_REGISTRY_HOST}/${SED_ACCOUNT}/${SED_REPO}:${SED_TAG}
