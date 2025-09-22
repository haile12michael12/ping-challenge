import { useState } from 'react'
import './App.css'
import { sendEchoMessage } from './services/EchoService'

function App() {
  const [message, setMessage] = useState('')
  const [response, setResponse] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  const handleSubmit = async (e) => {
    e.preventDefault()
    if (!message.trim()) return

    setLoading(true)
    setError('')
    
    try {
      const result = await sendEchoMessage(message)
      setResponse(result.message)
    } catch (err) {
      console.error('Error:', err)
      setError('Failed to send message. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="container">
      <h1>gRPC Echo Service</h1>
      <div className="card">
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="message">Enter a message:</label>
            <input
              type="text"
              id="message"
              value={message}
              onChange={(e) => setMessage(e.target.value)}
              placeholder="Type your message here"
              disabled={loading}
            />
          </div>
          <button type="submit" disabled={loading || !message.trim()}>
            {loading ? 'Sending...' : 'Send Message'}
          </button>
        </form>

        {error && <div className="error">{error}</div>}

        {response && (
          <div className="response">
            <h3>Response from Server:</h3>
            <p>{response}</p>
          </div>
        )}
      </div>
    </div>
  )
}

export default App
