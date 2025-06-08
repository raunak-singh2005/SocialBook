<?php

// Profile class handles user profile related operations
class Profile
{
	/**
	 * Retrieves a user's profile by their ID.
	 */
	public function getProfile($id)
	{
		// Sanitize the user ID to prevent SQL injection
		$id = addslashes($id);

		// Create a new Database instance
		$DB = new Database();

		// Prepare the SQL query to fetch the user profile
		$query = "SELECT * FROM Users WHERE userid = '$id' LIMIT 1";

		// Execute the query and return the result
		return $DB->read($query);
	}
}