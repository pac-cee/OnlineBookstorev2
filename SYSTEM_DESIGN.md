# System Design: OnlineBookstore Web Application

---

## 1. Overview & Objectives
- Describe the purpose of the system and its intended users.
- State the main objectives (e.g., unified book platform, learning tools, scalability, security).

## 2. System Architecture Diagram
![System Architecture Diagram](docs/architecture_diagram.png)
*Sample: Shows user roles (Student, Teacher, Admin), Frontend (HTML/CSS/JS), Backend (PHP), Database (MySQL), and their interactions.*
- User requests flow from browser to backend, which interacts with the database and returns responses.
- Admin/teacher roles have additional access to management modules.

## 3. Component Descriptions
- **Frontend:** Technologies used, main pages/components, UI/UX principles.
- **Backend:** Technologies used, main controllers/services, authentication, business logic.
- **Database:** Schema overview, key tables, and relationships.

## 4. Data Flow Diagrams (DFD)
### Context Diagram (Sample)
| Actor        | Process            | Data Store      | Data Flow Description           |
|--------------|--------------------|-----------------|-------------------------------|
| User         | Register/Login     | Users           | Sends credentials, receives session |
| User         | Browse Catalog     | Books           | Requests book list/details         |
| User         | Place Order        | Orders          | Submits order, receives confirmation |
| User         | Take Quiz          | Quizzes         | Submits answers, receives score     |

### Level 1 DFD: Book Ordering
| Step | Description                          |
|------|--------------------------------------|
| 1    | User selects book and clicks 'Order' |
| 2    | Frontend sends order to backend      |
| 3    | Backend validates and stores order   |
| 4    | Order confirmation returned to user  |

## 5. Entity-Relationship Diagram (ERD)
| Entity      | Attributes                                      | Relationships                     |
|-------------|-------------------------------------------------|------------------------------------|
| Users       | id, username, password, email, role, ...        | 1-to-many Orders, Progress, Quizzes|
| Books       | id, title, author, price, description, ...      | 1-to-many Orders, AudioBooks, Quizzes|
| Orders      | id, user_id, book_id, order_date, ...           | Many-to-1 User, Many-to-1 Book     |
| AudioBooks  | id, book_id, audio_file, duration               | 1-to-1 Book                        |
| Quizzes     | id, book_id, questions, ...                     | Many-to-1 Book, Many-to-1 User     |
| Progress    | id, user_id, book_id, pages_read, ...           | Many-to-1 User, Many-to-1 Book     |

*For a visual ERD, see ![ERD Diagram](docs/erd_diagram.png)*

## 6. API Specifications
### Auth Endpoints
- **POST** `/api/register` — Register a user
  - Body: `{ username, password, email, role }`
  - Response: `{ success, user_id }`
- **POST** `/api/login` — Login
  - Body: `{ username, password }`
  - Response: `{ success, session_token }`

### Book Endpoints
- **GET** `/api/books` — List/search books
  - Query: `search`, `author`
  - Response: `[ { id, title, author, ... }, ... ]`
- **GET** `/api/books/{id}` — Book details
  - Response: `{ id, title, author, description, ... }`
- **POST** `/api/books` — Add book (admin)
  - Body: `{ title, author, ... }`
  - Response: `{ success, book_id }`

### Order Endpoints
- **POST** `/api/order` — Place order
  - Body: `{ user_id, book_id }`
  - Response: `{ success, order_id }`
- **GET** `/api/orders` — List user orders
  - Response: `[ { id, book_id, order_date, ... }, ... ]`

### Quiz Endpoints
- **GET** `/api/quizzes` — List quizzes
  - Response: `[ { id, book_id, ... }, ... ]`
- **POST** `/api/quizzes/attempt` — Submit answers
  - Body: `{ user_id, quiz_id, answers }`
  - Response: `{ success, score }`

## 7. Security Considerations
- **Authentication:** Use PHP sessions, regenerate session IDs on login, expire sessions on logout.
- **Password Storage:** Use `password_hash()` and `password_verify()`.
- **SQL Injection:** All queries use prepared statements.
- **XSS Protection:** Escape all user-generated content before display.
- **File Uploads:** Restrict allowed file types (e.g., PDF, MP3), sanitize filenames, store outside web root.
- **Role-Based Access:** Check user role before allowing admin/teacher actions.
- **HTTPS:** Enforce HTTPS in production.

## 8. Scalability & Performance
- **Database:** Use indexes on frequently queried columns (e.g., `user_id`, `book_id`).
- **Caching:** Cache book catalog and static resources.
- **Load Balancing:** Deploy behind a load balancer for high traffic.
- **Frontend:** Minify JS/CSS, lazy-load images, use CDN for static assets.
- **Async Tasks:** Offload heavy tasks (e.g., audio processing) to background jobs.

## 9. Deployment Diagram
![Deployment Diagram](docs/deployment_diagram.png)
*Sample: Shows web server, database server, file storage, optional CDN, and user devices.*

## 10. Appendix
- Glossary, references, and links to related documents (README, API docs, etc.).

---

> **Tip:** Use draw.io, Lucidchart, or similar tools to create diagrams. Include images or links to diagrams as needed.
