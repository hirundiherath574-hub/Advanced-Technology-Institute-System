<?php
// api/students/search.php - Search students by NIC, name, or index number
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;
use App\Auth\Middleware;

header('Content-Type: application/json');

// Check authentication and permissions
try {
    Middleware::check('STUDENT_READ');
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    // Get search query
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';

    if (empty($query)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Search query is required. Use ?q=search_term'
        ]);
        exit;
    }

    // Minimum search length for performance
    if (strlen($query) < 2) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Search query must be at least 2 characters long'
        ]);
        exit;
    }

    $student = new Student();

    // Get optional filters
    $filters = [];

    if (isset($_GET['department']) && !empty($_GET['department'])) {
        $filters['department'] = $_GET['department'];
    }

    if (isset($_GET['mode']) && !empty($_GET['mode'])) {
        $filters['mode'] = strtoupper(trim($_GET['mode']));
    }

    if (isset($_GET['batch']) && !empty($_GET['batch'])) {
        $filters['batch'] = trim($_GET['batch']);
    }

    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }

    if (isset($_GET['academic_status']) && !empty($_GET['academic_status'])) {
        $filters['academic_status'] = $_GET['academic_status'];
    }

    // Get search type preference (optional)
    $searchType = isset($_GET['type']) ? $_GET['type'] : 'all';

    // Limit results for performance
    $limit = isset($_GET['limit']) ? min(intval($_GET['limit']), 100) : 20;

    // Perform search
    $results = $student->search($query, $filters, $searchType, $limit);

    // Determine what was likely searched for
    $searchMetadata = determineSearchType($query);

    $response = [
        'success' => true,
        'query' => $query,
        'search_type' => $searchType,
        'detected_type' => $searchMetadata['type'],
        'filters' => $filters,
        'results' => $results,
        'count' => count($results),
        'limit' => $limit
    ];

    // Add search suggestions if no results found
    if (empty($results)) {
        $response['suggestions'] = generateSearchSuggestions($query, $student);
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Search failed: ' . $e->getMessage()
    ]);
}

/**
 * Determine the likely search type based on query pattern
 */
function determineSearchType($query) {
    $query = trim($query);

    // NIC pattern (old: 123456789V, new: 123456789012)
    if (preg_match('/^[0-9]{9}[VvXx]$/', $query) || preg_match('/^[0-9]{12}$/', $query)) {
        return ['type' => 'nic', 'confidence' => 'high'];
    }

    // Index number pattern (KUR/DEPT/YEAR/MODE/NNNN)
    if (preg_match('/^KUR\/[A-Za-z]{2,10}\/[0-9]{4}\/[FPfp]\/[0-9]{4}$/', $query)) {
        return ['type' => 'index', 'confidence' => 'high'];
    }

    // Partial index number
    if (preg_match('/^KUR\//', $query)) {
        return ['type' => 'index', 'confidence' => 'medium'];
    }

    // Email pattern
    if (filter_var($query, FILTER_VALIDATE_EMAIL)) {
        return ['type' => 'email', 'confidence' => 'high'];
    }

    // Phone number pattern
    if (preg_match('/^[0-9+\-\s()]{10,15}$/', $query)) {
        return ['type' => 'phone', 'confidence' => 'medium'];
    }

    // Name pattern (contains letters and spaces)
    if (preg_match('/^[a-zA-Z\s.\']+$/', $query)) {
        return ['type' => 'name', 'confidence' => 'medium'];
    }

    return ['type' => 'mixed', 'confidence' => 'low'];
}

/**
 * Generate search suggestions when no results found
 */
function generateSearchSuggestions($query, $student) {
    $suggestions = [];

    // Try partial NIC search
    if (is_numeric($query) && strlen($query) >= 5) {
        $suggestions[] = "Try searching with the complete NIC number";
    }

    // Try partial index search
    if (stripos($query, 'KUR') !== false) {
        $suggestions[] = "Make sure the index number format is correct (e.g., KUR/IT/2324/F/0001)";
    }

    // Name search suggestions
    if (preg_match('/[a-zA-Z]/', $query)) {
        $suggestions[] = "Try searching with different name variations (full name, initials, etc.)";
        $suggestions[] = "Check spelling and try partial name search";
    }

    // General suggestions
    $suggestions[] = "Use filters (department, status) to narrow down results";
    $suggestions[] = "Try searching with at least 2 characters";

    return array_slice($suggestions, 0, 3); // Limit to 3 suggestions
}