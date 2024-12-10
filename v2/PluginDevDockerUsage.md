## Setup
1. Run `./docker.sh`
2. Go to http://localhost:8000/ in your browser.
3. Follow the WordPress page's setup guide create your user
4. After logging in to the WordPress admin go to "Plugins" > "Add New" > "Upload Plugin"
5. Zip up all file in this directory expect "docker-instance-files"
6. Upload this on the page from step 4. above
7. Make sure to active the plugin on the Plugins page
8. Your done, happy editing!

## Editing
1. Test changes by editing files under docker-instance-files/plugins/
  - Modify these directly on your host machine, changes take effect immediately!
2. Make sure to get your changes in to the original source to commit
  - Careful not to re-upload your plugin, it will overwrite files docker-instance-files/plugins/
  