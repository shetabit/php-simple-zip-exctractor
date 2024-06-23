# php-simple-zip-exctractor
A lightweight PHP script that allows you to upload and extract a ZIP file to a designated directory on your server. It includes built-in authentication to ensure only authorized users can access this functionality.

Benefits:

- Simplified Uploads: Upload your entire project (e.g., Vue or React built locally) as a single ZIP file, eliminating the need for manual file management through a server file manager.
- Streamlined Workflow: Save time and effort by avoiding the tedious process of uploading individual files through a server interface.
- Secure Uploads: The script incorporates authentication to prevent unauthorized access to file uploads and also throttling which prevents brute force attacks.
- Lightweight and Efficient: The script operates without requiring additional databases or temporary files, keeping your server footprint minimal.

Requirements:
- PHP with Zip extension enabled (https://www.php.net/manual/en/zip.installation.php)

Getting Started:

(Assuming you have downloaded the php-simple-zip-extractor script)

- Upload the script: Upload the php-simple-zip-extractor.php file to your desired server location.
- Configure authentication: (Specificy username and password in the file)
- Access the script: Navigate to the script's location in your web browser (e.g., http://yourdomain.com/php-simple-zip-extractor.php).
- Upload and Extract: Use the provided interface to upload your ZIP file and initiate the extraction process. The extracted files will be placed in the designated directory.

Further Enhancements (Optional):

- Implement progress tracking for larger ZIP files.
- Allow users to specify the destination directory for extracted files.

![Screenshot_2021-03-15_17-00-59](https://user-images.githubusercontent.com/54190980/111161502-79416800-85b0-11eb-9025-6297fd77c953.png)
