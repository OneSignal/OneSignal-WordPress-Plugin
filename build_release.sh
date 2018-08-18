# Note: Keep trailing slash to copy contents of dir, but not dir itself
WORDPRESS_GIT_SRC_PATH="."
DESTINATION_PATH="../onesignal-free-web-push-notifications/trunk/"
RELEASE_ARCHIVE_FILENAME="onesignal-free-web-push-notifications.zip"

# Prevent accidental rm -rf issues if running as root
if (( $EUID == 0 )); then
  echo "Please do not run this script as root for removing directory safety reasons."
  exit
fi

# Using exclude .* excludes dot files and dot directories like .git, .vscode
if [[ $DESTINATION_PATH != *onesignal-free-web-push-notifications/trunk/ ]]; then
 echo "Script was going to remove ${DESTINATION_PATH}, but quitting because destination path unexpectedly does not end in ...onesignal-free-web-push-notifications/trunk/. Exiting to prevent removing unexpected directory."
 exit
fi

echo "Building Release Version of OneSignal WordPress Plugin"
echo "──────────────────────────────────────────────────────"
echo ""

echo "Removing destination folder '${DESTINATION_PATH}'."
rm -rf $DESTINATION_PATH

echo "Creating new empty destination folder '${DESTINATION_PATH}'."
mkdir -p $DESTINATION_PATH

echo "Copying contents of source directory '${WORDPRESS_GIT_SRC_PATH}' to destination directory '${DESTINATION_PATH}'."
rsync --archive --exclude=".*" --exclude="build_release.sh" --exclude="*.zip" --exclude="onesignal-free-web-push-notifications" $WORDPRESS_GIT_SRC_PATH $DESTINATION_PATH

echo "Creating archive of release contents as '${RELEASE_ARCHIVE_FILENAME}' in source directory '${WORDPRESS_GIT_SRC_PATH}'."
last_dir=$(pwd)
cd ${WORDPRESS_GIT_SRC_PATH}
zip -qr -x ".*" -x="build_release.sh" -x="*.zip" -x="onesignal-free-web-push-notifications" ${RELEASE_ARCHIVE_FILENAME} ./*
cd $last_dir