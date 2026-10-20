FROM node:24-alpine

WORKDIR /app

COPY db/etl/package.json db/etl/package-lock.json ./
RUN npm ci --omit=dev

COPY db/etl/ ./

ENTRYPOINT ["node", "migrate.mjs"]
CMD ["--truncate"]
