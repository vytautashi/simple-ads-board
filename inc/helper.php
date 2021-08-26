<?php
class Redirect
{
    const HOME = '/';
    const LOGIN = '/login';
    const LOGOUT = '/logout';
    const ADMIN_PANEL = '/my-ads';

    public static function to_homepage()
    {
        header('Location: ' . self::HOME);
        exit;
    }

    public static function to_login()
    {
        header('Location: ' . self::LOGIN);
        exit;
    }

    public static function to_logout()
    {
        header('Location: ' . self::LOGOUT);
        exit;
    }

    public static function to_admin_panel()
    {
        header('Location: ' . self::ADMIN_PANEL);
        exit;
    }

    public static function to_custom_url(string $url)
    {
        header('Location: ' . $url);
        exit;
    }
}

class Common
{
    public static function shorten_description(string $description, int $size = 340): string
    {
        if (strlen($description) <= $size) {
            return $description;
        }
        $raw_description = substr($description, 0, $size);
        $last_word_position = strrpos($raw_description, ' ');
        $out_description = substr($raw_description, 0, $last_word_position) . " ...";

        return $out_description;
    }

    public static function create_slug($string)
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $string);
        return $slug;
    }

    public static function generate_token()
    {
        return bin2hex(random_bytes(16));
    }

    public static function pagination_html(int $page): string
    {
        $page_html = '<a href="/?p=' . ($page + 1) . '">next</a>';

        if ($page > 1) {
            $page_html = '<a href="/?p=' . ($page - 1) . '">prev</a> | ' . $page_html;
        }

        return $page_html;
    }

    // Removes spaces in between html tags
    public static function optimise_html_content(string $content): string
    {
        return preg_replace('/\>\s+\</m', '><', $content);
    }
}

class Account
{
    const SECURE_KEY = 'ef344r343r28uuptytvyu';

    public static function auth()
    {
        return isset($_SESSION['email']);
    }

    public static function login(string $email, string $password, $conn): bool
    {
        $stmt = $conn->prepare("SELECT * FROM account WHERE `email` = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return false;
        }

        $account = $result->fetch_assoc();
        $password_hash = hash('sha256', $password . $account['salt'] . self::SECURE_KEY, true);
        if ($account['password'] !== $password_hash) {
            return false;
        }

        return true;
    }

    public static function create_account(string $email, string $password, $conn): bool
    {
        $salt = random_bytes(32);
        $password_hash = hash('sha256', $password . $salt . self::SECURE_KEY, true);

        $stmt = $conn->prepare("INSERT INTO account (`email`, `password`, `salt`) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $password_hash, $salt);

        return $stmt->execute();
    }

    public static function get_account(string $email, $conn)
    {
        $stmt = $conn->prepare("SELECT * FROM account WHERE `email` = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return null;
        }
        $account = $result->fetch_assoc();

        return $account;
    }
}

class Advertisement
{
    public static function get_multiple_advertisements($conn, int $page = 1, int $ads_per_page = 15)
    {
        if ($page < 1) {
            $page = 1;
        }
        $page_start = ($page - 1) * $ads_per_page;
        $stmt = $conn->prepare("SELECT * FROM advertisement WHERE enabled = true ORDER BY id DESC LIMIT ?, ?");
        $stmt->bind_param("ii", $page_start, $ads_per_page);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_advertisement_admin($conn, int $advertisement_id, int $account_id)
    {
        $stmt = $conn->prepare("SELECT * FROM advertisement WHERE id = ? AND account_id = ? LIMIT 1");
        $stmt->bind_param("ii", $advertisement_id, $account_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public static function get_multiple_advertisements_admin($conn, int $account_id)
    {
        $stmt = $conn->prepare("SELECT * FROM advertisement WHERE `account_id` = ? ORDER BY id DESC");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function update($conn, string $title, string $description, int $advertisement_id, int $account_id): bool
    {
        $stmt = $conn->prepare("UPDATE advertisement SET `title` = ?, `description` = ? WHERE id = ? AND account_id = ? LIMIT 1");
        $stmt->bind_param("ssii", $title, $description, $advertisement_id, $account_id);

        self::create_advertisement_file($advertisement_id, $title, $description);

        return $stmt->execute();
    }

    public static function create_advertisement(string $title, string $description, int $account_id, $conn)
    {
        $stmt = $conn->prepare("INSERT INTO advertisement (`title`, `description`, `account_id`) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $description, $account_id);
        if (!$stmt->execute()) {
            return null;
        }
        $advertisement_id = $stmt->insert_id;

        self::create_advertisement_file($advertisement_id, $title, $description);

        return $advertisement_id;
    }

    public static function create_advertisement_file($id, $title, $description)
    {
        $content_for_file = self::generate_advertisement_template($id, $title, $description);
        $filename = $id . '.html';
        $myfile = fopen('ad/' . $filename, "w") or die("Unable to open file!");
        fwrite($myfile, $content_for_file);
        fclose($myfile);
    }

    public static function delete(int $id, int $account_id, $conn): bool
    {
        $stmt = $conn->prepare("DELETE FROM advertisement WHERE id = ? AND account_id = ? LIMIT 1");
        $stmt->bind_param("ii", $id, $account_id);
        if (!$stmt->execute()) {
            return false;
        }

        unlink('ad/' . $id . '.html');

        return true;
    }

    public static function generate_advertisement_template($id, $title, $description)
    {
        $html_page = '<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>' . htmlentities($title) . '</title>
        <link type="text/css" rel="stylesheet" href="/styles.css">
    </head>

    <body>
        <nav>
            <ul>
                <li><a href="/">Advertisements</a></li>
                <li><a href="/my-ads">My ads</a></li>
                <li><a href="/about">About</a></li>
            </ul>
        </nav>
        <main>
            <h1>' . htmlentities($title) . '</h1>
            <p>' .  nl2br(htmlentities($description)) . '</p>
        </main>
    </body>

</html>';
        // return Common::optimise_html_content($html_page);
        return $html_page;
    }

    public static function generate_ad_summary_html(int $id, string $title, string $description, string $create_date, bool $is_admin_panel, string $delete_token = ''): string
    {
        $admin_options = '';
        if ($is_admin_panel) {
            $admin_options = '<a href="/my-ads/delete?id=' . $id . '&delete_token=' . $delete_token . '">Delete</a> | <a href="/my-ads/edit?id=' . $id . '">Edit</a>';
        }

        $ad_url = '/ad/' . htmlentities(Common::create_slug($title)) . '-' . $id;

        return '<div><h4><a href="' . $ad_url . '">' . htmlentities($title) . '</a> ' .
            '<time>' . substr($create_date, 0, 10) . '</time></h4>' . $admin_options  .
            '<p>' .  htmlentities(Common::shorten_description($description)) . '</p></div>';
    }
}
