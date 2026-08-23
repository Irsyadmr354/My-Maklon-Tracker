<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PindahBuktiCommand extends Command
{
    protected $signature = 'maklon:pindah-bukti';

    protected $description = 'Memindahkan seluruh isi storage/app/public/bukti ke disk privat storage/app/bukti';

    public function handle(Filesystem $files): int
    {
        $sumber = storage_path('app/public/bukti');
        $tujuan = storage_path('app/bukti');

        if (! is_dir($sumber)) {
            $this->info('Folder sumber tidak ditemukan. 0 file dipindahkan.');

            return self::SUCCESS;
        }

        $files->ensureDirectoryExists($tujuan);

        $jumlah = 0;

        foreach ($files->allFiles($sumber) as $berkas) {
            $relatif    = $berkas->getRelativePathname();
            $tujuanFile = $tujuan . DIRECTORY_SEPARATOR . $relatif;

            if (file_exists($tujuanFile)) {
                $this->line("Lewati (sudah ada di tujuan): {$relatif}");

                continue;
            }

            $files->ensureDirectoryExists(dirname($tujuanFile));
            $files->move($berkas->getPathname(), $tujuanFile);
            $jumlah++;
        }

        $this->info("Selesai. {$jumlah} file dipindahkan ke storage/app/bukti.");

        return self::SUCCESS;
    }
}
