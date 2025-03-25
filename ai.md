
When AI improves and can do all work that humans did before, people may be looking for meaning in life. I want to make a webpage were people can share their lifestyle, so that all can get inspiration from this.

- Full text search for content (widgets)
- Generate categories based on most popular tags
  - add some default tags as long as no user tags are added

- Start page
  - show content of people that the user follow
    - use a simple algorithm for deciding which content is shown for now, we will improve that later
    - use no pagination but a "load more" button
  - Trending content

- Personal profile page (visible)
  - avatar and username
  - summary: short lifestyle summary
  - people I follow
  - content area: the user can add widgets with a short text
    - widgets may also contain: a single image or a video or a weblink
    - as well as:
      - tags
      - a larger text can be added (plain or html) that is visible on click
    - images can be uploaded or linked
    - video can only be linked (e.g. youtube)
    - any weblink can be added
      - if it is google maps the widget show the map
    - widgets can be added, edited or removed
    - widgets can be liked and users can comment
      - use no pagination but a "load more" button

- Private page for each user with all of his previously liked content
- ability to follow someone

- Settings page (currently empty)
- Register and Login
  - Simple mail based register and login system
  - Login with Google (provide setup instructions in a readme)

Make it usable on PG and smartphone (responsive). Make a ready to use app and choose a nice layout and design based on bootrap 5.3 with own styles.

- In this app we use mysql for all data, provide setup sql script
- for file uploads use a folder
  - replace the file name with a unique id
  - save the original file name along with the id in the database
- Error handling
  - Types of errors
    - PHP errors on server
    - ajax comminucation errors
    - errors that appear in js code
  - all of these must result in a error message: replace the whole body of the page with a user friendly error message
