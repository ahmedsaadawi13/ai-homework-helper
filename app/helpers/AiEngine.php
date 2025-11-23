<?php
// FILE: /app/helpers/AiEngine.php

/**
 * AI Engine helper (Mock Implementation)
 * Simulates AI responses for homework help, quiz generation, and explanations
 */
class AiEngine
{
    /**
     * Generate homework help response
     *
     * @param string $question Question text
     * @param string $subject Subject name
     * @param string $gradeLevel Grade level
     * @return array AI response with content, steps, examples
     */
    public static function generateHomeworkHelp($question, $subject, $gradeLevel = '')
    {
        $startTime = microtime(true);

        // Mock AI response based on subject
        $response = [
            'content' => self::getMainExplanation($question, $subject),
            'step_by_step' => self::getStepByStep($question, $subject),
            'examples' => self::getExamples($subject),
            'related_topics' => self::getRelatedTopics($subject),
        ];

        $endTime = microtime(true);
        $processingTime = round(($endTime - $startTime) * 1000);

        // Simulate token usage
        $tokensUsed = rand(100, 500);

        return [
            'response' => $response,
            'tokens_used' => $tokensUsed,
            'processing_time_ms' => $processingTime > 0 ? $processingTime : rand(800, 2000),
        ];
    }

    /**
     * Generate quiz questions
     *
     * @param string $subject Subject name
     * @param string $topic Topic
     * @param int $numQuestions Number of questions
     * @param string $difficulty Difficulty level
     * @return array Quiz questions
     */
    public static function generateQuiz($subject, $topic, $numQuestions = 5, $difficulty = 'medium')
    {
        $questions = [];

        $templates = self::getQuizTemplates($subject);

        for ($i = 0; $i < $numQuestions; $i++) {
            $template = $templates[array_rand($templates)];
            $questions[] = [
                'question_type' => $template['type'],
                'question_text' => $template['question'],
                'options' => $template['options'] ?? null,
                'correct_answer' => $template['correct_answer'],
                'explanation' => $template['explanation'],
                'points' => 1,
                'order_num' => $i + 1,
            ];
        }

        return [
            'questions' => $questions,
            'tokens_used' => rand(200, 600),
            'processing_time_ms' => rand(1000, 3000),
        ];
    }

    /**
     * Evaluate student answer
     *
     * @param string $question Question
     * @param string $correctAnswer Correct answer
     * @param string $studentAnswer Student answer
     * @return array Evaluation result
     */
    public static function evaluateAnswer($question, $correctAnswer, $studentAnswer)
    {
        // Simple evaluation logic
        $isCorrect = trim(strtolower($studentAnswer)) === trim(strtolower($correctAnswer));

        $feedback = $isCorrect
            ? "Correct! Well done."
            : "Not quite right. The correct answer is: " . $correctAnswer;

        return [
            'is_correct' => $isCorrect,
            'feedback' => $feedback,
            'score' => $isCorrect ? 1 : 0,
        ];
    }

    /**
     * Generate topic explanation
     *
     * @param string $topic Topic name
     * @param string $subject Subject
     * @return string Explanation
     */
    public static function explainTopic($topic, $subject)
    {
        return "This is an AI-generated explanation of {$topic} in {$subject}. " .
               "The topic covers fundamental concepts and practical applications. " .
               "Students should understand the key principles and be able to apply them to solve problems.";
    }

    /**
     * Get main explanation based on subject
     *
     * @param string $question Question
     * @param string $subject Subject
     * @return string
     */
    private static function getMainExplanation($question, $subject)
    {
        $explanations = [
            'Math' => "To solve this problem, we need to apply mathematical principles systematically. Let's break down the question and identify the key components.",
            'Mathematics' => "To solve this problem, we need to apply mathematical principles systematically. Let's break down the question and identify the key components.",
            'Physics' => "This physics problem involves understanding the fundamental laws and applying the appropriate formulas. Let's analyze the given information.",
            'Chemistry' => "In chemistry, we need to understand the chemical properties and reactions involved. Let's examine the molecular structure and interactions.",
            'Biology' => "This biological concept involves understanding living organisms and their processes. Let's explore the mechanisms at work.",
            'English' => "To answer this language question, we should analyze the grammar rules, vocabulary, and context. Let's examine the structure.",
            'Science' => "This scientific concept requires understanding the natural phenomena and applying scientific method. Let's investigate step by step.",
        ];

        return $explanations[$subject] ?? "Let's solve this step by step using logical reasoning and subject knowledge.";
    }

    /**
     * Get step-by-step solution
     *
     * @param string $question Question
     * @param string $subject Subject
     * @return string
     */
    private static function getStepByStep($question, $subject)
    {
        return "Step 1: Read and understand the problem carefully\n" .
               "Step 2: Identify what is given and what needs to be found\n" .
               "Step 3: Apply the relevant formula or concept\n" .
               "Step 4: Solve systematically\n" .
               "Step 5: Verify your answer makes sense";
    }

    /**
     * Get example problems
     *
     * @param string $subject Subject
     * @return string
     */
    private static function getExamples($subject)
    {
        $examples = [
            'Math' => "Example 1: If x + 5 = 12, then x = 7\nExample 2: If 2y = 10, then y = 5",
            'Mathematics' => "Example 1: If x + 5 = 12, then x = 7\nExample 2: If 2y = 10, then y = 5",
            'Physics' => "Example 1: F = ma, if m=10kg and a=5m/s², then F=50N\nExample 2: Speed = Distance/Time",
            'Chemistry' => "Example 1: H2O → 2H + O (water molecule)\nExample 2: NaCl → Na+ + Cl- (ionic bond)",
        ];

        return $examples[$subject] ?? "Example 1: Problem demonstration\nExample 2: Another similar case";
    }

    /**
     * Get related topics
     *
     * @param string $subject Subject
     * @return string
     */
    private static function getRelatedTopics($subject)
    {
        return "Related topics: Basic concepts, Advanced applications, Practice problems";
    }

    /**
     * Get quiz templates by subject
     *
     * @param string $subject Subject
     * @return array
     */
    private static function getQuizTemplates($subject)
    {
        $templates = [
            [
                'type' => 'mcq',
                'question' => 'What is the primary concept in this topic?',
                'options' => '["Option A", "Option B (Correct)", "Option C", "Option D"]',
                'correct_answer' => 'Option B (Correct)',
                'explanation' => 'This is correct because it addresses the fundamental principle.',
            ],
            [
                'type' => 'true_false',
                'question' => 'The main principle applies in all cases. True or False?',
                'options' => '["True", "False"]',
                'correct_answer' => 'True',
                'explanation' => 'This is a universal principle in the subject.',
            ],
            [
                'type' => 'mcq',
                'question' => 'Which formula should be used to solve this type of problem?',
                'options' => '["Formula A", "Formula B", "Formula C (Correct)", "Formula D"]',
                'correct_answer' => 'Formula C (Correct)',
                'explanation' => 'Formula C is specifically designed for this type of problem.',
            ],
        ];

        return $templates;
    }
}
