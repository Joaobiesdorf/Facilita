<?php
// Function to calculate distance between two coordinates in kilometers using Haversine formula
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    }
    
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    
    return ($miles * 1.609344); // to kilometers
}

// Function to compute average rating
function getAverageRating($pdo, $provider_id) {
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE provider_id = ?");
    $stmt->execute([$provider_id]);
    $result = $stmt->fetch();
    return $result['avg_rating'] ? round($result['avg_rating'], 1) : 0;
}
?>
