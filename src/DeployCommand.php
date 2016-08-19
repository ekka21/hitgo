<?php

namespace Hitgo\Installer\Console;

use RuntimeException;
use GuzzleHttp\Client;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DeployCommand extends Command
{
    protected $hitgo_api  = 'https://api.hitgo.io/new/instant';
    protected $hitgo_home = './.hitgo/apps';
    protected $web_roots  = ['public', 'htdocs', 'index.php', 'index.html', 'index.htm'];
    protected $ignore_dir = ['.hitgo', '.DS_Store'];

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('deploy')
             ->setDescription('Deploy an application to hitgo server');
    }

    /**
     * Execute the command.
     *
     * @param  OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$output->writeln('<info>Deploying your app to hitgo server</info>');

        $app_name = $this->strRandom().'.tar.gz';
        $this->createHome();
      //  $this->showProgress($output, 5);
        $this->zip($app_name);
        $upload = $this->upload($app_name);

        $this->appReady($output, str_replace('.tar.gz','',$app_name));
    }

    protected function createHome()
    {
        $this->shell('mkdir -p ' . $this->hitgo_home);
    }

    protected function zip($app_name)
    {
        $this->shell('tar -cvzf ' . $this->hitgo_home . '/' . $app_name . $this->exclude() . ' ./');
    }

    protected function exclude()
    {
        $out_str    = '';

        foreach($this->ignore_dir as $dir)
        {
            $out_str .= ' --exclude=' . $dir;
        }

        return $out_str;
    }

    protected function upload($app_name)
    {
        $request = (new Client)->request('POST', $this->hitgo_api, [
            'multipart' =>[
                    [
                       'name'     => 'app',
                       'contents' => fopen($this->hitgo_home .'/'. $app_name, 'r'),
                       'filename' => $app_name,
                   ],[
                       'name'      => 'web_root',
                       'contents'  => '',
                       'filename'  => $this->webRoot(),
                   ]

                ]
        ]);

        return $request;
    }

    protected function webRoot()
    {
        foreach(scandir('./') as $dir)
        {
            if ( in_array($dir, $this->web_roots))
                return $dir;
        }

        return '';
    }

    protected function shell($command)
    {
        $process = new Process($command);
        $process->run();

        if ( !$process->isSuccessful())
        {
             throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    protected function appReady($output, $app_name)
    {
    	$output->writeln('<comment>http://'.$app_name.'.hitgo.io is ready and don\'t forget to be awesome!</comment>');
    }

    protected function showProgress($output, $rows)
    {
        $progressBar = new ProgressBar($output, $rows);
        $progressBar->setBarCharacter('<fg=magenta>=</>');
        $progressBar->setProgressCharacter("\xF0\x9F\x8D\xBA");

        for ($i = 0; $i<$rows; $i++)
        {
            usleep(300000);
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln('');
    }

    protected function strRandom($length = 10)
    {
    	$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return strtolower(substr(str_shuffle(str_repeat($pool, $length)), 0, $length));
    }

    protected function randomWords($length = 5)
    {
        return strtolower((new Client)->request('GET', 'http://randomword.setgetgo.com/get.php?len=' . $length)->getBody() .'-'. (new Client)->request('GET', 'http://randomword.setgetgo.com/get.php?len=' . $length)->getBody());
    }

}
