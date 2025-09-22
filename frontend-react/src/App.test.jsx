import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import App from './App';
import * as EchoService from './services/EchoService';

// Mock the EchoService
vi.mock('./services/EchoService', () => ({
  sendEchoMessage: vi.fn()
}));

describe('App Component', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders the echo form correctly', () => {
    render(<App />);
    
    expect(screen.getByText('gRPC Echo Service')).toBeInTheDocument();
    expect(screen.getByLabelText('Message:')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Send' })).toBeInTheDocument();
  });

  it('handles form submission correctly', async () => {
    // Mock successful response
    const mockResponse = {
      getMessage: () => 'Echo: Test message',
      getTimestamp: () => '2023-05-01T12:00:00Z'
    };
    
    EchoService.sendEchoMessage.mockResolvedValue(mockResponse);
    
    render(<App />);
    
    // Fill in the form and submit
    const input = screen.getByLabelText('Message:');
    fireEvent.change(input, { target: { value: 'Test message' } });
    
    const button = screen.getByRole('button', { name: 'Send' });
    fireEvent.click(button);
    
    // Check loading state
    expect(screen.getByText('Sending...')).toBeInTheDocument();
    
    // Wait for response
    await waitFor(() => {
      expect(screen.getByText('Response:')).toBeInTheDocument();
      expect(screen.getByText('Echo: Test message')).toBeInTheDocument();
      expect(screen.getByText('Timestamp: 2023-05-01T12:00:00Z')).toBeInTheDocument();
    });
    
    // Verify service was called with correct parameters
    expect(EchoService.sendEchoMessage).toHaveBeenCalledWith('Test message');
  });

  it('handles errors correctly', async () => {
    // Mock error response
    EchoService.sendEchoMessage.mockRejectedValue(new Error('Connection error'));
    
    render(<App />);
    
    // Fill in the form and submit
    const input = screen.getByLabelText('Message:');
    fireEvent.change(input, { target: { value: 'Test message' } });
    
    const button = screen.getByRole('button', { name: 'Send' });
    fireEvent.click(button);
    
    // Wait for error message
    await waitFor(() => {
      expect(screen.getByText('Error: Connection error')).toBeInTheDocument();
    });
  });
});