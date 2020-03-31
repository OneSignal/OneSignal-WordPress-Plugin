# The purpose of this script is to provide write access to the wordpress plugins folder for
#   both the host machine and the docker container itself.
# This makes plugin testing much easy as the the file in the plugins folder can be directly
#   edited on the host machine's IDE.
# This means no more needing to copy files from host->container or using vim in the container!

export $(xargs < .env)

USER_ID=`id -u`
GROUP_ID=`id -g`

# Starting docker images without allowing them to execute anything yet.
# This is so we can correctly set the permission below before it starts creating files
echo "Starting $CONTAINER in --no-start mode"
docker-compose up --no-start $CONTAINER
docker-compose up --detach $CONTAINER

echo "Wait for all wordpress files to be comes available"
docker exec -ti $CONTAINER /bin/bash -c 'until [[ -f .htaccess ]]; do sleep 1; done'

echo "Allowing Wordpress container to modify any files in the docker-instance-file/plugins folder"
docker exec -ti $CONTAINER /bin/bash -c "usermod -u ${USER_ID} www-data"
docker exec -ti $CONTAINER /bin/bash -c "groupmod -g ${GROUP_ID} www-data"
echo "Restart container for permsision settings to take effect"
docker container restart $CONTAINER

echo "Updating '/var/www' folder ownership"
docker exec -ti $CONTAINER /bin/bash -c 'chown -R www-data:www-data /var/www'

echo "Started Scuessfully!"
echo "──────────────────────────────────────────────────────"

echo "Tailing logs"
docker-compose up