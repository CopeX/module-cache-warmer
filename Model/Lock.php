<?php

namespace Magenest\CacheWarmer\Model;

class Lock
{
    const LOCK_FILE_NAME = "var/varnish-warmer.lock";
    const LOCK_FILE_PERMISSION = 'a+';

    /**
     * @var \Magenest\CacheWarmer\Logger\Logger
     */
    private $logger;

    protected $file = null;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * Lock constructor.
     * @param \Magenest\CacheWarmer\Logger\Logger $logger
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magenest\CacheWarmer\Logger\Logger $logger,
        \Magento\Framework\Filesystem\DirectoryList $directoryList
    ) {
        $this->logger = $logger;
        $this->directoryList = $directoryList;
    }

    function lock()
    {
        $this->openFile();

        if(!flock($this->file, LOCK_EX | LOCK_NB, $wouldblock)) {
            if ($wouldblock) {
                if(file_exists($this->getFileName())) {
                    $pid = fread($this->file, filesize($this->getFileName()));
                }
                $this->logger->error("FAILED to acquire lock. A lock already exist for pid $pid");
                throw new \Exception("FAILED to acquire lock. A lock already exist for pid $pid");
            } else {
                $this->logger->error("FAILED to acquire lock for unknow reasons");
                throw new \Exception("FAILED to acquire lock for unknow reasons");
            }
        }

        ftruncate($this->file, 0);
        fwrite($this->file, getmypid());
        fflush($this->file);

        return true;
    }


    function unlock()
    {
        $this->openFile();

        if(!flock($this->file, LOCK_UN))  {
            $this->logger->error("FAILED to release lock");
            throw new \Exception("FAILED to release lock");
        }

        ftruncate($this->file, 0);
        fflush($this->file);

        return true;
    }

    protected function openFile() {
        if(empty($this->file)) {
            try {
                $this->file = fopen($this->getFileName(), self::LOCK_FILE_PERMISSION);
            } catch (\Exception $e) {
                $this->logger->error('Can\'t open lockfile.');
                throw new \Exception($e->getMessage());
            }
        }
    }

    protected function getFileName() {
        return $this->directoryList->getRoot() . '/' . self::LOCK_FILE_NAME;
    }
};