/**
     * Get message history
     */
    public function GetMessageHistory(ContextInterface $ctx, HistoryRequest $in): HistoryResponse
    {
        $this->logger->debug('Message history request received', [
            'limit' => $in->getLimit(),
            'offset' => $in->getOffset()
        ]);

        $limit = $in->getLimit() > 0 ? $in->getLimit() : 10;
        $offset = $in->getOffset() >= 0 ? $in->getOffset() : 0;
        
        $history = $this->messageHistory->getHistory($limit, $offset);
        $response = new HistoryResponse();
        
        foreach ($history as $entry) {
            $historyEntry = new MessageHistoryEntry();
            $historyEntry->setId($entry['id']);
            $historyEntry->setOriginalMessage($entry['original_message']);
            $historyEntry->setProcessedMessage($entry['processed_message']);
            $historyEntry->setTimestamp($entry['timestamp']);
            
            // Add metadata
            foreach ($entry['metadata'] as $key => $value) {
                $historyEntry->getMetadata()->offsetSet($key, $value);
            }
            
            $response->getMessages()->append($historyEntry);
        }
        
        $response->setTotalCount(count($this->messageHistory->getHistory(1000, 0)));
        
        return $response;
    }

    /**
     * Search message history
     */
    public function SearchMessageHistory(ContextInterface $ctx, SearchRequest $in): HistoryResponse
    {
        $this->logger->debug('Message history search request received', [
            'query' => $in->getQuery()
        ]);

        $query = $in->getQuery();
        $limit = $in->getLimit() > 0 ? $in->getLimit() : 10;
        
        $results = $this->messageHistory->searchMessages($query);
        $results = array_slice($results, 0, $limit);
        
        $response = new HistoryResponse();
        
        foreach ($results as $entry) {
            $historyEntry = new MessageHistoryEntry();
            $historyEntry->setId($entry['id']);
            $historyEntry->setOriginalMessage($entry['original_message']);
            $historyEntry->setProcessedMessage($entry['processed_message']);
            $historyEntry->setTimestamp($entry['timestamp']);
            
            // Add metadata
            foreach ($entry['metadata'] as $key => $value) {
                $historyEntry->getMetadata()->offsetSet($key, $value);
            }
            
            $response->getMessages()->append($historyEntry);
        }
        
        $response->setTotalCount(count($results));
        
        return $response;
    }