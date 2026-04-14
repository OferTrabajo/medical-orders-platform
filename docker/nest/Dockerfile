FROM node:20-alpine

WORKDIR /app

COPY nest-validator/package*.json ./
RUN npm install

COPY nest-validator ./

EXPOSE 3000

CMD ["npm", "run", "start:dev"]


