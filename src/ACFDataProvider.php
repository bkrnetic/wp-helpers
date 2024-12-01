<?php
declare(strict_types=1);

namespace bkrnetic\wpHelpers;

class ACFDataProvider
{
    const string OPTION = 'option';

    private static ?ACFDataProvider $instance = null;

    private string $prefix = '';

    /**
     * @var array<string, mixed>
     */
    private array $fields = [];

    /**
     * DataProvider constructor.
     */
    private function __construct()
    {
    }

    public static function getInstance(): ACFDataProvider
    {
        if (self::$instance === null) {
            self::$instance = new ACFDataProvider();
        }

        return self::$instance;
    }

    public function getOptionField(string $name, bool $prefixed = true): mixed
    {
        return $this->getField($name, self::OPTION, $prefixed);
    }

    public function getField(
        string $name,
        null|string|int $postID = null,
        bool $prefixed = true
    ): mixed {
        $postID = $postID !== null ? $postID : get_the_ID();
        $key = ($prefixed ? $this->prefix : '' ) . $name;

        $cacheKey = 'field_' . $key . '_' . $postID;
        $fieldsCacheKey = 'fields_' . $postID;

        if (isset($this->fields[$cacheKey])) {
            return $this->fields[$cacheKey];
        } elseif (isset($this->fields[$fieldsCacheKey][$key])) {
            return $this->fields[$fieldsCacheKey][$key];
        }

        $this->fields[$cacheKey] = get_field($key, $postID);
        return $this->fields[$cacheKey];
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function clearPrefix(): self
    {
        $this->prefix = '';
        return $this;
    }

    public function clearCache(): self
    {
        $this->fields = [];
        return $this;
    }

    /**
     * @param null|string|int $postID
     * @return array<string, mixed>|bool
     */
    public function getFields(
        null|string|int $postID = null,
    ): array|bool {
        $postID = $postID !== false ? $postID : get_the_ID();
        $key = 'fields_' . $postID;
        if (isset($this->fields[$key])) {
            return $this->fields[$key];
        }
        $this->fields[$key] = get_fields($postID);

        return $this->fields[$key];
    }

    public function getUserField(
        string $name,
        null|string|int $userID = null,
        bool $prefixed = true,
    ): mixed {
        if ($userID === null && is_single()) {
            $userID = get_the_author_meta('ID');
        }

        if ($userID !== null) {
            return $this->getField($name, 'user_' . $userID, $prefixed);
        }

        return '';
    }
}
