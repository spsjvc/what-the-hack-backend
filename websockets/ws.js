'use strict'
const path = require('path')
const DeepstreamServer = require('deepstream.io')
const C = DeepstreamServer.constants

const options = {
    http: {
      options: {
        port: process.env.WS_PORT
      }
    }
}

const server = new DeepstreamServer(options)

// Start Deepstream.io WebSockets
server.start()
