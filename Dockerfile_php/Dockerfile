FROM php:7-fpm-alpine

# Enable mysqli
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Install ffmpeg for mp3 edits
RUN apk update && apk add --no-cache ffmpeg

# Change the uid to 1000 to allow file writes to host filesystem
RUN apk --no-cache add shadow && usermod -u 1000 www-data

# Install audiowaveform, see https://github.com/realies/audiowaveform-docker/blob/master/Dockerfile
env commit 3d07c8e
RUN apk add --no-cache --virtual build-dependencies cmake curl git jq make && \
 apk --no-cache add boost-dev g++ gcc gd-dev libid3tag-dev libmad-dev libsndfile-dev && \
 git clone -n https://github.com/bbc/audiowaveform.git && \
 cd audiowaveform && \
 git checkout ${commit} && \
 curl -fL# $(curl -s "https://api.github.com/repos/google/googletest/releases/latest" | jq -r .tarball_url) -o googletest.tar.gz && \
 tar -xf googletest.tar.gz && \
 ln -s google*/google* . && \
 mkdir build && \
 cd build && \
 cmake .. && \
 cd ../build && \
 make -j $(nproc) && \
 make install && \
 apk del build-dependencies && \
 rm -rf /var/cache/apk/* && \
 rm -rf /var/www/html/audiowaveform

# Edit and install production php.ini but change maxupload to 256M
ARG upload_max_filesize=256
# post_max_size must be larger than upload_max_filesize
ARG post_max_size=288
# memory_limit _should_ be larger than post_max_size (maybe)
ARG memory_limit=320
RUN sed -ri 's/^(upload_max_filesize = )[0-9]+(M.*)$/\1'${upload_max_filesize}'\2/' "$PHP_INI_DIR/php.ini-production"
RUN sed -ri 's/^(post_max_size = )[0-9]+(M.*)$/\1'${post_max_size}'\2/' "$PHP_INI_DIR/php.ini-production"
RUN sed -ri 's/^(memory_limit = )[0-9]+(M.*)$/\1'${memory_limit}'\2/' "$PHP_INI_DIR/php.ini-production"
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install Autosub
RUN apk add py-pip git --no-cache && pip install pip install git+https://github.com/agermanidis/autosub.git
