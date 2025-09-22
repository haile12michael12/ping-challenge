import { EchoServiceClient } from '../proto/echo_grpc_web_pb';
import { EchoRequest } from '../proto/echo_pb';

// Create a client instance
const client = new EchoServiceClient('http://localhost:8080');

export const sendEchoMessage = (message) => {
  return new Promise((resolve, reject) => {
    const request = new EchoRequest();
    request.setMessage(message);
    
    client.echo(request, {}, (err, response) => {
      if (err) {
        console.error('Error sending echo message:', err);
        reject(err);
        return;
      }
      
      resolve({
        message: response.getMessage(),
        originalMessage: response.getOriginalMessage(),
        timestamp: response.getTimestamp(),
        processingTimeMs: response.getProcessingTimeMs()
      });
    });
  });
};