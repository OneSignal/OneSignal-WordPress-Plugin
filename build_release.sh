WORDPRESS_GIT_SRC_PATH="."
DESTINATION_PATH="../onesignal-free-web-push-notifications/trunk/"
RELEASE_ARCHIVE_FILENAME="onesignal-free-web-push-notifications.zip"

# Prevent accidental rm -rf issues if running as root
if (( EUID == 0 )); then
  echo "Please do not run this script as root for removing directory safety reasons."
  exit 1
fi

# Using exclude .* excludes dot files and dot directories like .git, .vscode
if [[ $DESTINATION_PATH != *onesignal-free-web-push-notifications/trunk/ ]]; then
 echo "Script was going to remove ${DESTINATION_PATH}, but quitting because destination path unexpectedly does not end in ...onesignal-free-web-push-notifications/trunk/. Exiting to prevent removing unexpected directory."
 exit 1
fi

echo "Building Release Version of OneSignal WordPress Plugin"
echo "──────────────────────────────────────────────────────"

echo "Removing destination folder '${DESTINATION_PATH}'."
rm -rf $DESTINATION_PATH

echo "Creating new empty destination folder '${DESTINATION_PATH}'."
mkdir -p $DESTINATION_PATH

# The --delete option remove files from destination that are not present in source
exclude_options=(
    "--exclude=.git/"
    "--exclude=.env"
    "--exclude=.gitignore"
    "--exclude=.github"
    "--exclude=.vscode/"
    "--exclude=build_release.sh"
    "--exclude=*.zip"
    "--exclude=onesignal-free-web-push-notifications"
    "--exclude=onesignal-free-web-push-notifications.zip"
    "--exclude=CONTRIBUTING.md"
    "--exclude=LICENSE"
    "--exclude=docker*"
    "--exclude=PluginDevDockerUsage.md"
    "--exclude=README.md"
    "--exclude=index.php"
    "--exclude=views/css/*.scss"
    "--exclude=views/css/callout.css"
    "--exclude=views/css/link.css"
    "--exclude=views/css/link.css"
    "--exclude=views/css/semantic-ui"
)
rsync --archive --delete "${exclude_options[@]}" $WORDPRESS_GIT_SRC_PATH/ $DESTINATION_PATH

# Add new unversioned directories to SVN
svn status $DESTINATION_PATH | grep "^\?" | awk '{print $2}' | xargs -I {} svn add {}

echo "Creating archive of release contents as '${RELEASE_ARCHIVE_FILENAME}' in source directory '${WORDPRESS_GIT_SRC_PATH}'."
cd $WORDPRESS_GIT_SRC_PATH
zip -qr -x ".*" -x="build_release.sh" -x="*.zip" -x="onesignal-free-web-push-notifications" ${RELEASE_ARCHIVE_FILENAME} ./*
cd -
