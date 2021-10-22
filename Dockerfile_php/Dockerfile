FROM php:7-fpm-alpine

# Enable mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Install ffmpeg for mp3 edits
RUN apk update && apk add --no-cache ffmpeg

# Change the uid to 1000 to allow file writes to host filesystem
RUN apk --no-cache add shadow && usermod -u 1000 www-data

# Install audiowaveform, see https://github.com/realies/audiowaveform-docker/blob/master/Dockerfile
RUN apk --no-cache add libstdc++
ENV COMMIT b6b61b0
RUN apk add --no-cache boost-dev boost-static cmake curl g++ gcc gd-dev git \
    jq libid3tag-dev libmad-dev libpng-static libsndfile-dev libvorbis-static make zlib-static
RUN apk add --no-cache autoconf automake libtool gettext && \
    curl -fL# "$(curl -s 'https://api.github.com/repos/xiph/flac/tags' | jq -r '. | first | .tarball_url')" -o flac.tar.gz && \
    mkdir flac && \
    tar -xf flac.tar.gz -C flac --strip-components=1 && \
    cd flac && \
    ./autogen.sh && \
    ./configure --enable-shared=no && \
    make -j $(nproc) && \
    make install
RUN git clone -n https://github.com/bbc/audiowaveform.git && \
    cd audiowaveform && \
    git checkout ${COMMIT} && \
    curl -fL# "$(curl -s 'https://api.github.com/repos/google/googletest/releases/latest' | jq -r '.tarball_url')" -o googletest.tar.gz && \
    mkdir googletest && \
    tar -xf googletest.tar.gz -C googletest --strip-components=1 && \
    mkdir build && \
    cd build && \
    cmake -D ENABLE_TESTS=1 -D BUILD_STATIC=1 .. && \
    make -j $(nproc) && \
    # /audiowaveform/build/audiowaveform_tests && \
    make install && \
    strip /usr/local/bin/audiowaveform

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

# Install mail server
RUN apk add ssmtp --no-cache
COPY gmail.conf /etc/ssmtp/ssmtp.conf
# Here is the gmail configuration (or change it to your private smtp server)
RUN echo "mailhub=smtp.gmail.com:587" >> /etc/ssmtp/ssmtp.conf
RUN echo "UseTLS=YES" >> /etc/ssmtp/ssmtp.conf
RUN echo "UseSTARTTLS=YES" >> /etc/ssmtp/ssmtp.conf
RUN echo "FromLineOverride=YES" >> /etc/ssmtp/ssmtp.conf
# Set up php sendmail config
RUN echo "sendmail_path=sendmail -i -t" >> /usr/local/etc/php/conf.d/php-sendmail.ini

# install fpcalc
RUN apk add chromaprint --no-cache

#install mp3splt
RUN apk add mp3splt --no-cache
