FROM redis:alpine

# Tambahkan metadata
LABEL maintainer="leonardobryan32@gmail.com"

# Run redis-server

CMD ["redis-server"]