# C2SML_Website

The demo is live at: [https://c2sml.cn/submission/index.php](https://c2sml.cn/submission/index.php).  
(Note: The website currently does not have an SSL certificate, so you may encounter a security warning. Please proceed by ignoring the warning.)

## How to Run

1. **Install the basic environment**  
   Ensure that Apache, MySQL, and PHP are installed on your server.

2. **Clone this repository**  
   Use the following command to clone the repository to your local machine:
   ```bash
   git clone https://github.com/your-repo/C2SML_Website.git
   ```

3. **Configure email settings**  
   Navigate to the `utils/` directory, locate the file `email_config[delete_this].json`, rename it to `email_config.json`, and fill in the SMTP server details. You can find a free SMTP server from [this list](https://www.emailtooltester.com/en/blog/free-smtp-servers/).

4. **Set correct permissions for the database directory and files**  
   If necessary, grant the required permissions to the database directory and its files:
   ```bash
   chmod 777 db
   chmod 777 db/conference_main.db
   chmod 777 db/users.db
   chmod 777 db/verification_code.db
   ```

5. **Run the application**  
   Open `index.php` in your browser, and you're all set! Enjoy the demo.

---

# Experience Log

## GPT-Assisted Workflow

1. Define the broad requirements.
2. Refine the requirements down to specific instructions (e.g., "click the button below to navigate to the index page").
   1. You can use GPT to assist in refining the requirements.
   2. Refer to `refined-design-v1.md` in the same directory for guidance.
3. Have GPT write the code for the first page and debug it.
4. Continuously debug the first page until it meets all logic, design, and animation requirements (important).
5. Once the first page is polished, pass the high-quality page and the next set of requirements to GPT to generate the next page.
6. Debug the new page, then repeat step 5 until the module is complete.
7. Repeat step 2 for the next module, refining the requirements as needed.

### Core Principles

1. Avoid the "minimum viable product" mindset.
2. Understand GPT's nature: "Garbage in, garbage out" and "Gold in, gold out."
3. Clear requirements and proper references will lead to fast, high-quality results.

## Architecture and Dialogue Insights

1. Do not let the output exceed 2000 tokens (~250 lines), or it will start ignoring comments, functions, and cause logical issues.
   1. Develop and test in modular chunks, and extract complex functions to the `utils` folder.
   2. If the code is under 200 lines, feel free to add multiple pieces of logic. If it exceeds 200 lines, only add one piece of logic at a time.
2. Do not rely entirely on GPT. If a requirement is not met after several attempts, you will need to learn the syntax yourself.
   1. GPT has limited context awareness; complex logic that spans multiple files may still require manual intervention.
   2. GPT can assist in explaining syntax, but always ask for examples.

---

# C2SML_Website

本网站为导师主办会议登记使用。我之前没有任何前端开发经验，使用1周的课余时间在GPT的辅助下完成了环境搭建，代码编写以及调试。

本Demo主要实现的功能为用户的注册、登录和密码找回，为后续开发确立视觉风格的基础。

演示地址: [https://c2sml.cn/submission/index.php](https://c2sml.cn/submission/index.php)  
（注意：网站目前没有 SSL 证书，因此您可能会遇到安全警告，请忽略警告继续访问。）

## 运行步骤

1. **安装基本环境**  
   确保您的服务器上已经安装了 Apache、MySQL 和 PHP（可使用WAMP or LAMP来迅速完成环境配置）。

2. **克隆此仓库**  
   运行以下命令将仓库克隆到本地机器：
   ```bash
   git clone https://github.com/your-repo/C2SML_Website.git
   ```

3. **配置邮件设置**  
   进入 `utils/` 目录，找到文件 `email_config[delete_this].json`，将其重命名为 `email_config.json`，并填写 SMTP 服务器的详细信息。您可以从[这个列表](https://www.emailtooltester.com/en/blog/free-smtp-servers/)中找到免费的 SMTP 服务器。

4. **设置数据库目录和文件的权限**  
   如有必要，应用以下权限到数据库目录及其文件：
   ```bash
   chmod 777 db
   chmod 777 db/conference_main.db
   chmod 777 db/users.db
   chmod 777 db/verification_code.db
   ```

5. **运行应用程序**  
   在浏览器中打开 `index.php`，尽情探索吧！

---

# 经验记录

## GPT辅助工作流

1. 确认大体需求。
2. 细化需求，到具体指令（例如：“点击下方的按钮跳转到index页面”）。
   1. 可使用GPT辅助细化。
   2. 可参考同目录下的 `refined-design-v1.md`。
3. 让GPT编写第一个页面的代码，并进行调试。
4. **反复调试第一个页面，直到完全符合逻辑、美术、动画的要求（重要）**。
5. 将调试完成的高质量页面和下一个页面的需求传输给GPT，生成高质量的新页面。
6. 对新页面进行调试，然后重复步骤5，直到完成该模块的开发。
7. 重复步骤2，细化下一个模块的需求。

### 核心心法

1. 明晰“喂失得失，喂金得金”的GPT特性。
2. 摒弃“最简可行”思想，GPT速度很快，不需要敏捷开发，不断迭代。
3. 利用GPT快速复制已经打磨完美的代码，先把y轴顶到头，然后直接快速填满x轴。

## 架构及对话经验

1. 不要让输出超过2000个token（约250行代码），否则会明显出现注释丢失、函数丢失、逻辑混乱的情况。
   1. 模块化开发，模块化测试，复杂功能抽离到 `utils` 文件夹。
   2. 代码小于200行时，可以放心添加多个逻辑；代码大于200行时，每次只添加一个逻辑。
2. 不要全信GPT，如果一个需求多次未能实现，仍然需要自学语法。
   1. GPT只能掌握有限的信息，复杂的跨文件逻辑报错时，仍然需要自己手动处理。
   2. 可以让GPT辅助解释语法，但要记得多问一些例子。
