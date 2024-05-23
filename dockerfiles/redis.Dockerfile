FROM redis:alpine

# Tambahkan metadata
LABEL maintainer="HaiPigGi"

# Run redis-server

CMD ["redis-server"]