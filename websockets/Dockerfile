FROM node:8
WORKDIR /ws
COPY . .
ENV WS_PORT 8080
RUN npm install
EXPOSE 8080 6020
CMD [ "node", "ws.js" ]