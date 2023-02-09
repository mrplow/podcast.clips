FROM php:7-fpm-alpine

# Enable mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Install ffmpeg for mp3 edits
RUN apk update && apk add --no-cache ffmpeg

# Change the uid to 1000 to allow file writes to host filesystem
RUN apk --no-cache add shadow && usermod -u 1000 www-data

# Install audiowaveform, see https://github.com/realies/audiowaveform-docker/blob/master/Dockerfile
RUN apk --no-cache add libstdc++
ENV COMMIT 78126bd
RUN apk add --no-cache autoconf automake g++ gcc libtool make nasm ncurses-dev && \
	wget https://downloads.sourceforge.net/project/lame/lame/3.100/lame-3.100.tar.gz && \
	tar -xf lame-3.100.tar.gz && \
	cd lame-3.100 && \
	# fix for parallel builds
	mkdir -p libmp3lame/i386/.libs && \
	# fix for pic build with new nasm
	sed -i -e '/define sp/s/+/ + /g' libmp3lame/i386/nasm.h && \
	aclocal && automake --force --add-missing && \
	./configure \
		--build=$CBUILD \
		--host=$CHOST \
		--prefix=/usr \
		--enable-nasm \
		--disable-mp3x \
		--disable-shared \
		--with-pic && \
	make -j $(nproc) && \
	make test && \
	make install
RUN apk add --no-cache autoconf automake g++ gcc libtool gettext git make && \
	git clone https://github.com/xiph/opus && \
	cd opus && \
	./autogen.sh && \
	./configure \
		--prefix=/usr \
		--sysconfdir=/etc \
		--localstatedir=/var \
		--enable-custom-modes && \
	make -j $(nproc) && \
	make check && \
	make install
RUN apk add --no-cache cmake g++ gcc git samurai && \
	git clone https://github.com/xiph/ogg && \
	cd ogg && \
	cmake -B build -G Ninja \
		-DCMAKE_INSTALL_PREFIX=/usr \
		-DCMAKE_INSTALL_LIBDIR=lib \
		-DBUILD_SHARED_LIBS=False \
		-DCMAKE_BUILD_TYPE=Release \
		$CMAKE_CROSSOPTS && \
	cmake --build build -j $(nproc) && \
	ctest -j $(nproc) && \
	cmake --install build
RUN apk add --no-cache autoconf automake libtool g++ gcc gettext git !libiconv make pkgconfig && \
	git clone https://github.com/xiph/flac && \
	cd flac && \
	./autogen.sh && \
	./configure \
		--prefix=/usr \
		--enable-shared=no \
		--enable-ogg \
		--disable-rpath \
		--with-pic && \
	make -j $(nproc) && \
	make check || true && \
	make install
RUN apk add --no-cache alsa-lib-dev cmake git flac-dev libvorbis-dev linux-headers python3 samurai && \
	git clone https://github.com/libsndfile/libsndfile && \
	cd libsndfile && \
	cmake -B build -G Ninja \
		-DBUILD_SHARED_LIBS=OFF \
		-DCMAKE_BUILD_TYPE=MinSizeRel \
		-DCMAKE_INSTALL_PREFIX=/usr \
		-DENABLE_MPEG=ON && \
	cmake --build build -j $(nproc) && \
	cd build && \
	CTEST_OUTPUT_ON_FAILURE=TRUE ctest -E write_read_test_sd2 && \
	cd .. && \
	cmake --install build
RUN apk add --no-cache cmake g++ gcc git samurai zlib-dev && \
	git clone https://codeberg.org/tenacityteam/libid3tag && \
	cd libid3tag && \
	cmake -B build -G Ninja \
		-DBUILD_SHARED_LIBS=OFF \
		-DCMAKE_BUILD_TYPE=MinSizeRel \
		-DENABLE_TESTS=YES \
		-DCMAKE_INSTALL_PREFIX=/usr \
		-DCMAKE_INSTALL_LIBDIR=lib && \
	cmake --build build -j $(nproc) && \
	cd build && \
	CTEST_OUTPUT_ON_FAILURE=TRUE ctest && \
	cd .. && \
	cmake --install build
RUN apk add --no-cache boost-dev boost-static cmake g++ gcc gd-dev git libgd libmad-dev libpng-dev libpng-static libvorbis-static make zlib-dev zlib-static && \
	git clone -n https://github.com/bbc/audiowaveform.git && \
	cd audiowaveform && \
	git checkout ${COMMIT} && \
	git clone https://github.com/google/googletest && \
	mkdir build && \
	cd build && \
	cmake -DCMAKE_CXX_STANDARD=14 -D ENABLE_TESTS=1 -D BUILD_STATIC=1 .. && \
	make -j $(nproc) && \
	/audiowaveform/build/audiowaveform_tests || true && \
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

# install mp3splt
RUN apk add mp3splt --no-cache

# install word cloud
#RUN pip install wordmesh && python -m spacy download en_core_web_md
