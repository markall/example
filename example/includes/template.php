<?php

function get_template_content($template_name) {
    $template_path = "./templates/" . $template_name; // Adjust path if needed
    if (file_exists($template_path)) {
        return file_get_contents($template_path);
    } else {
        return "<p>Error: Template not found: $template_path </p>";
    }
}
