```markdown
- Backend:
  + Mô hình cơ sở: [Simple PHP MVC Framework (Mr. Ken)](https://forum.vdevs.net/forum/threads/simple-php-mvc-framework.14963/page-1) và 1 ít đồ chơi của DorewSite =))
  + Template engine: **Twig 3.7.0**
  + Yêu cầu: **PHP 7.4**, extension: **iconv()**

- Giao diện: Sử dụng [Just The Docs](https://just-the-docs.com)
  ![Giao diện](https://i.imgur.com/MSwdCuK.png)

- Chức năng:
  + Đăng nhập, chỉ một quản trị viên.
  + Tạo/sửa/xóa/view chuyên mục, bài viết, chapter.
  + Tìm kiếm lọc từ khóa theo tiêu đề/nội dung của bài viết.
  + Đếm lượt xem theo check session.

- Một số hình ảnh để ae thẩm du tinh thần:
  ![Hình ảnh 1](https://i.imgur.com/jKdsW6R.png)
  ![Hình ảnh 2](https://i.imgur.com/Sk69tjb.png)
  ![Hình ảnh 3](https://i.imgur.com/Rlbl8WD.png)
  ![Hình ảnh 4](https://i.imgur.com/Wqrac54.png)
  ![Hình ảnh 5](https://i.imgur.com/qh0Ky78.png)
  ![Hình ảnh 6](https://i.imgur.com/sjpK4nB.png)

**HƯỚNG DẪN SỬ DỤNG:**
  + Tải code bên dưới về, upload lên host và giải nén.
  + Chạy lệnh `composer install` để cài đặt các thư viện cần thiết.
  + Upload file **banhbao_blog.sql** lên MariaDB.
  + Cấu hình: **/configs**.
  + Trỏ tên miền về **/www** hoặc thư mục bao gồm trang web của bạn.
```