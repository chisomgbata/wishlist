# Wishlist App API

## Overview

The Wishlist App API provides a backend service for managing user wishlists in an e-commerce environment. It allows
users to register, log in, view products, and add or remove products from their personal wishlists. This API is built
with Laravel and follows RESTful principles.

## Features

* User Registration and Authentication (Sanctum-based tokens)
* Product Listing and Viewing
* Wishlist Management (Add, View, Remove items)
* Clear, consistent JSON responses
* Input validation and error handling

## Requirements

* PHP 8.2 or above
* Composer
* A database supported by Laravel (e.g., MySQL, PostgreSQL, SQLite) default is SQLite
* Configured `.env` file (based on `.env.example`)

## Setup Instructions

You can setup the project locally or use a hosted version at `wishlist.chisom.shop`

You can also use a visual api documentation `wishlist.chisom.shop/docs/api`

The Open API spec is also available at `wishlist.chisom.shop/docs/api.json`

If you prefer Postman you can import the Postman collection at [postman.json](http://wishlist.chisom.shop/postman.json)

1. **Clone the Repository:**
   ```bash
   git clone <your-repository-url>
   cd wishlist-app
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   ```

3. **Environment Configuration:**
    * Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    * Open the `.env` file and configure your database connection details (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`,
      `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) or you can leave it as is to use SQLite.
    * Ensure `APP_URL` is set correctly (e.g., `http://localhost:8000` if using `php artisan serve`).

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Run Database Migrations:**
   This will create the necessary tables in your database.
   ```bash
   php artisan migrate
   ```

6. **Seed the Database (Optional but Recommended):**
   This command will populate the `products` table with sample data.
   ```bash
   php artisan migrate --seed
   ```

7. **Start the Development Server:**
   ```bash
   php artisan serve
   ```
   The API will typically be available at `http://localhost:8000/api/v1/`.

## API Endpoint Documentation

There is a Postman Collection available at [postman.json](public/postman.json) for testing the API endpoints.

All API endpoints are prefixed with `/api/v1`.

All responses are in JSON format.

Successful responses for creating resources will typically return a `201 Created` status.

Successful responses for fetching resources will typically return a `200 OK` status.

Successful responses for deleting resources will typically return a `200 OK` status with a message or a `204 No Content`
status.

---

### 1. Authentication Endpoints (`/auth`)

#### 1.1. Register New User

* **Endpoint:** `POST /auth/register`
* **Description:** Registers a new user in the system.
* **Headers:**
    * `Accept: application/json`
    * `Content-Type: application/json`
* **Request Body:**
    ```json
    {
        "name": "John Doe",
        "email": "john.doe@example.com",
        "password": "yourSecurePassword123",
        "password_confirmation": "yourSecurePassword123"
    }
    ```
* **Parameters:**
    * `name` (string, required): User's full name.
    * `email` (string, required, email, unique): User's email address.
    * `password` (string, required, min:8, confirmed): User's password.
    * `password_confirmation` (string, required): Confirmation of the password.
    * **Success Response (201 Created):**
        ```json
        {
            "message": "User registered successfully.",
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john.doe@example.com",
                "created_at": "2025-05-09T10:58:18.000000Z" 
             }
        }
        ```
* **Error Responses:**
    * **422 Unprocessable Entity:** If validation fails (e.g., email already taken, password too short, passwords don't
      match).
        ```json
        {
            "message": "The given data was invalid.",
            "errors": {
                "email": ["The email has already been taken."],
                "password": ["The password must be at least 8 characters."]
            }
        }
        ```

#### 1.2. Login User

* **Endpoint:** `POST /auth/login`
* **Description:** Logs in an existing user and returns an API token.
* **Headers:**
    * `Accept: application/json`
    * `Content-Type: application/json`
* **Request Body:**
    ```json
    {
        "email": "john.doe@example.com",
        "password": "yourSecurePassword123"
    }
    ```
* **Parameters:**
    * `email` (string, required, email): User's email address.
    * `password` (string, required): User's password.
* **Success Response (200 OK):**
    ```json
    {
        "message": "User logged in successfully.",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john.doe@example.com",
            "created_at": "2025-05-09T10:58:18.000000Z"
        },
        "token": "your_api_token_here"
    }
    ```
* **Error Responses:**
    * **401 Unauthorized:** If credentials are invalid.
        ```json
        {
            "message": "Invalid credentials."
        }
        ```
    * **422 Unprocessable Entity:** If validation fails (e.g., email format incorrect, password missing).
        ```json
        {
            "message": "The given data was invalid.",
            "errors": {
                "email": ["The email field must be a valid email address."]
            }
        }
        ```
    * **429 Too Many Requests:** If the rate limit for login attempts is exceeded.

#### 1.3. Get Authenticated User Details

* **Endpoint:** `GET /auth/user`
* **Description:** Retrieves the details of the currently authenticated user.
* **Authentication:** Requires Bearer Token.
* **Headers:**
    * `Accept: application/json`
    * `Authorization: Bearer <your_api_token>`
* **Success Response (200 OK):**
    ```json
    {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john.doe@example.com",
            "created_at": "2025-05-09T10:58:18.000000Z"

        }
    }
    ```
* **Error Responses:**
    * **401 Unauthorized:** If the token is missing, invalid, or expired.

#### 1.4. Logout User

* **Endpoint:** `POST /auth/logout`
* **Description:** Logs out the currently authenticated user by revoking their current API token.
* **Authentication:** Requires Bearer Token.
* **Headers:**
    * `Accept: application/json`
    * `Authorization: Bearer <your_api_token>`
* **Success Response (200 OK):**
    ```json
    {
        "message": "User logged out successfully."
    }
    ```
* **Error Responses:**
    * **401 Unauthorized:** If the token is missing, invalid, or expired.

---

### 2. Product Endpoints (`/products`)

#### 2.1. List Available Products

* **Endpoint:** `GET /products`
* **Description:** Retrieves a list of all available products. Supports pagination.
* **Headers:**
    * `Accept: application/json`
* **Success Response (200 OK):**
    ```json
    {
        "data": [
            {
                "id": 1,
                "name": "Awesome T-Shirt",
                "description": "A very comfortable and stylish t-shirt.",
                "price": 1999
            },
            {
                "id": 2,
                "name": "Cool Mug",
                "description": "The perfect mug for your morning coffee.",
                "price": 999
            }
          
        ],
        "links": {
            "first": "http://localhost/api/v1/products?page=1",
            "last": "http://localhost/api/v1/products?page=...",
            "prev": null,
            "next": "http://localhost/api/v1/products?page=2"
        },
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": "...",
            "path": "http://localhost/api/v1/products",
            "per_page": 10, 
            "to": 10,
            "total": "..."
        }
    }
    ```

#### 2.2. Get a Specific Product

* **Endpoint:** `GET /products/{product_id}`
* **Description:** Retrieves details for a single product by its ID.
* **Headers:**
    * `Accept: application/json`
* **URL Parameters:**
    * `product_id` (integer, required): The ID of the product to retrieve.
* **Success Response (200 OK):**
    ```json
    {
        "data": {
            "id": 1,
            "name": "Awesome T-Shirt",
            "description": "A very comfortable and stylish t-shirt.",
            "price": 1999
        }
    }
    ```
* **Error Responses:**
    * **404 Not Found:** If the product with the specified ID does not exist.
        ```json
        {
            "message": "No query results for model [App\\Models\\Product] {product_id}"
        }
        ```

---

### 3. Wishlist Endpoints (`/wishlist`)

These endpoints require user authentication.

#### 3.1. View User's Wishlist

* **Endpoint:** `GET /wishlist`
* **Description:** Retrieves all products currently in the authenticated user's wishlist.
* **Authentication:** Requires Bearer Token.
* **Headers:**
    * `Accept: application/json`
    * `Authorization: Bearer <your_api_token>`
* **Success Response (200 OK):**
    * If wishlist is empty:
        ```json
        {
            "data": []
        }
        ```
    * If wishlist has items:
        ```json
        {
            "data": [
                {
                    "id": 1,
                    "name": "Awesome T-Shirt",
                    "description": "A very comfortable and stylish t-shirt.",
                    "price": 1999
                },
                {
                    "id": 5,
                    "name": "Fancy Hat",
                    "description": "A very fancy hat.",
                    "price": 2499
                }
            
            ]
        }
        ```
* **Error Responses:**
    * **401 Unauthorized:** If the token is missing, invalid, or expired.

#### 3.2. Add Product to Wishlist

* **Endpoint:** `POST /wishlist`
* **Description:** Adds a specified product to the authenticated user's wishlist.
* **Authentication:** Requires Bearer Token.
* **Headers:**
    * `Accept: application/json`
    * `Content-Type: application/json`
    * `Authorization: Bearer <your_api_token>`
* **Request Body:**
    ```json
    {
        "product_id": 1
    }
    ```
* **Parameters:**
    * `product_id` (integer, required): The ID of the product to add.
* **Success Response (201 Created):**
    ```json
    {
        "message": "Product added to wishlist successfully."
    }
    ```
* **Error Responses:**
    * **401 Unauthorized:** If the token is missing, invalid, or expired.
    * **409 Conflict:** If the product is already in the user's wishlist.
        ```json
        {
            "message": "Product already in wishlist."
        }
        ```
    * **422 Unprocessable Entity:** If validation fails (e.g., `product_id` is missing, not an integer, or does not
      exist in the `products` table).
        ```json
        {
            "message": "The given data was invalid.",
            "errors": {
                "product_id": ["The selected product id is invalid."]
            }
        }
        ```

#### 3.3. Remove Product from Wishlist

* **Endpoint:** `DELETE /wishlist/{product_id}`
* **Description:** Removes a specified product from the authenticated user's wishlist.
* **Authentication:** Requires Bearer Token.
* **Headers:**
    * `Accept: application/json`
    * `Authorization: Bearer <your_api_token>`
* **URL Parameters:**
    * `product_id` (integer, required): The ID of the product to remove from the wishlist.
* **Success Response (200 OK):**
    ```json
    {
        "message": "Product removed from wishlist successfully."
    }
    ```
  Alternatively, a `204 No Content` response with an empty body is also acceptable.
* **Error Responses:**
    * **401 Unauthorized:** If the token is missing, invalid, or expired.
    * **404 Not Found:** If the product specified by `product_id` is not found in the user's wishlist or if the product
      ID itself is invalid/not found in the products table (depending on your controller's initial validation).
        ```json
        {
            "message": "Product not found in wishlist."
        }
        ```

---

## Testing

To run the automated tests (PHPUnit):

```bash
php artisan test
