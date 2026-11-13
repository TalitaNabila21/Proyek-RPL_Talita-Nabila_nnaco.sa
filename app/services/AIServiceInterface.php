<?php

interface AIServiceInterface {
    /**
     * Ask a question to the AI providing the system context.
     * 
     * @param string $systemContext The context (like products, prices) given to the AI.
     * @param string $userMessage The question from the customer.
     * @return string The response from the AI.
     */
    public function askQuestion(string $systemContext, string $userMessage): string;
}
