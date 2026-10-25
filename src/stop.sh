# Stop and remove the running findacat container
docker stop $(docker ps -q --filter ancestor=findacat)
docker rm $(docker ps -aq --filter ancestor=findacat)

# Optional: remove the image
# docker rmi findacat