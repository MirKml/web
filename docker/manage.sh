#!/bin/bash

# Simple script for easy management for pilulka.cz docker based project
# which I touched. It's mostly wrapper for docker-composer and docker command.
MAIN_DIR=$(dirname "$0")
PROJECT=mirinweb
CONTAINERS=( www dbm )
SCRIPT_NAME=${0##*/}

usage() {
cat << EOF
Usage: $SCRIPT_NAME [OPTIONS] [COMMAND]

Simple manager for $PROJECT docker stuff.
Works as simple wrapper and extender for docker-compose.

Options:

  --help       Display this help and exit.

Available command:
 
  list         List of available containers.
  buildImage   Rebuild necessary images, see "buildImage" for
               more informations about possible Docker images.     
  exec         Exec command in running container.
  go           Attach container shell.
  compose      Use docker-compose for other commands.
  rmStopped    Remove stopped containers
EOF
}

callCompose() {
    docker-compose -p $PROJECT -f $MAIN_DIR/docker-compose.yml "$@"
}

list() {
    echo "Available containers: ${CONTAINERS[@]}"
}

buildImage() {
    local images=( web dbm )
    local buildOptions=(
        "-t mirin:web $MAIN_DIR/www"
        "-t mirin:dbm $MAIN_DIR/dbm"
    )

    if [ "$#" -eq 0 ]; then
        echo "Builds the image according appropriate Dockerfile"
        echo "Usage $SCRIPT_NAME buildImage IMAGE_NAME|all"
        echo "Available Docker images: ${images[@]}"
        echo "If \"all\" is used as IMAGE_NAME, all images are built."
        return 0
    fi

    if [ "$1" == "all" ]; then
        for buildCommand in "${buildOptions[@]}"; do
            docker build $buildCommand
        done
        return 0
    fi

    local i
    for ((i=0; i<${#images[@]}; i++)); do
        if [ "$1" == "${images[i]}" ]; then
            docker build ${buildOptions[i]}
            return $?
        fi
    done

    echo "Cannot build docker image \"$1\", image is unknown."
    echo "Available Docker images: ${images[@]}"
    return 1
}

execCommand() {
    local containerID=$(getContainerID $1)
    if [[ "$containerID" == ----* ]]; then
        # no runnig container found, so we try to run command
        # via new container 
        callCompose run -T --rm "$@"
        return $?
    fi
    shift
    docker exec $containerID "$@"
}

# remove all stopped containers
removeStopped() {
    local containerIDs=()
    local containerNames=()
    while IFS= read -r line; do
        if [[ "$line" == *Exited* ]]; then
            # separator for columns in output from docker ps -a are spaces
            # so we can use string as array
            local columns=(${line});
            containerIDs+=(${columns[0]})
            containerNames+=(${columns[1]})
        fi
    done < <(docker ps -a --format "{{.ID}} {{.Names}} {{.Status}}")

    if [ ${#containerIDs[@]} -eq 0 ]; then
        echo "no stopped containers found"
        return 0
    fi

    echo "removing these stopped containers: ${containerNames[@]}"
    docker rm ${containerIDs[@]}
}

# get contaner ID by it's name from defined by docker-compose
getContainerID() {
    echo $(callCompose ps $1 | tail -n1 | cut -d' ' -f1)
}

# exec interarractive bash session with particular container
attachContainer() {
    local containerID=$(getContainerID $1)
    if [[ "$containerID" == ----* ]]; then
        echo "container $1 doesn't run, cannot attach into it"
        echo "start the container first (docker run or docker-compose up)"
        return 1
    fi
    docker exec -it $containerID /bin/bash
}

if [ "$#" -eq 0 ]; then
    usage
    exit 1
fi

case "$1" in
    --help)
        usage
        ;;
    list)
        list
        ;;
    buildImage)
        shift
        buildImage "$@" 
        ;;
    exec)
        if [ -z "$2" ]; then
           echo "exec: no container name supplied"
           echo "usage: $SCRIPT_NAME exec CONTAINER COMMAND"
           list
           exit 1
        fi
        if [ -z "$3" ]; then
           echo "exec: no command for execution supplied"
           echo "usage: $SCRIPT_NAME exec CONTAINER COMMAND"
           exit 1
        fi
        shift
        execCommand "$@"
        ;;
    compose)
        shift
        callCompose "$@"
        ;;
    rmStopped)
        removeStopped
        ;;
    go)
        if [ -z "$2" ]; then
           echo "go: no container name supplied"
           echo "usage: $SCRIPT_NAME go CONTAINER"
           list
           exit 1
        fi
        attachContainer $2
        ;;
    *)
        echo "unknown option or commad \"$1\" use --help for help"
        exit 1
        ;;
esac
