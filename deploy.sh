#EVN=Development

if [ -z "${EVN}" ];then
  echo "EVN NOT SET"
  exit
fi


APP_NAME=mhsh

IMAGE_PATH_EXT=/data/images
IMAGE_PATH_LOCAL=/www/images
DEPLOY_PATH=/www/wwwroot
PHP_PATH=/etc/init.d/php-fpm-73
TEST_EVN=Development

if [ -d "/data/images/" ];then
 IMAGE_PATH_TMP=${IMAGE_PATH_EXT}
else
 IMAGE_PATH_TMP=${IMAGE_PATH_LOCAL}
fi

echo "${EVN}"

if [ "${TEST_EVN}" = "${EVN}" ];then
 IMAGE_PATH_TMP=${IMAGE_PATH_TMP}/${APP_NAME}-test
 DEPLOY_PATH_TMP=${DEPLOY_PATH}/${APP_NAME}-test
else
 IMAGE_PATH_TMP=${IMAGE_PATH_TMP}/${APP_NAME}
 DEPLOY_PATH_TMP=${DEPLOY_PATH}/${APP_NAME}
fi
echo "IMAGE_PATH:${IMAGE_PATH_TMP}"
echo "DEPLOY_PATH:${DEPLOY_PATH_TMP}"

rm -rf ${DEPLOY_PATH_TMP}/backend/web/uploads
ln -sfn ${IMAGE_PATH_TMP}/backend/web/uploads ${DEPLOY_PATH_TMP}/backend/web/uploads
rm -rf ${DEPLOY_PATH_TMP}/public
ln -sfn ${IMAGE_PATH_TMP}/public ${DEPLOY_PATH_TMP}/public
php ${DEPLOY_PATH_TMP}/initm --env=${EVN} --overwrite=a
${PHP_PATH} restart