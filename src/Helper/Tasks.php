<?php
namespace Ssl\Helper;

use Ssl\Helper\MailerController as MailerController;


class Tasks {

    public function getAllFilesOfDir( $path, $filter ) {
        $rii = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path ) );

        $files = array();
        foreach( $rii as $file )
            if ( !$file->isDir() )
                $files[] = $file->getPathname();

        $files = array_filter( $files, function ( $var ) { return ( stripos( $var, $filter ) == true ); } );
        return $files;
    }

    public function copyAllFiles( $dir1, $dir2 ) {
        $files = glob( $dir1 . "*.*"  );
        foreach ($files as $file ) {
            copy( $file, $dir2 . basename( $file ) );
        }
    }

    public function moveAllFiles( $dir1, $dir2 ) {
        $files = glob( $dir1 . "*.*"  );

        if( !is_dir( $dir2 ) ) {
            mkdir( $dir2, 0775 );
        }

        foreach ($files as $file ) {
            rename( $file, $dir2 . basename( $file ) );
        }
    }

    public function moveSelectedFiles( $dir1, $extension, $dir2 ) {
        $files = glob( $dir1 . $extension );

        if( !is_dir( $dir2 ) ) {
            mkdir( $dir2, 0775 );
        }

        foreach( $files as $file ) {
            rename( $file, $dir2 . basename( $file ) . $extension );
        }
    }

    public function importCsv( $fileName='', $check = 0 ) {
        if( !file_exists( $fileName ) || !is_readable( $fileName ) )
        die( $fileName . " f:importCsv ** File not found or doesn't have sufficient permission while importing to csv.**");

        $delimiter = ',';
        $flag = 0;
        $header = NULL;
        $data = array();

        if ( ( $handle = fopen( $fileName, 'r' ) ) !== FALSE) {
            while ( ( $row = fgetcsv( $handle, 100000, $delimiter ) ) !== FALSE ) {
                if( !$header ) {
                    $header = $row;
                } else {
                    $data[] = array_combine( $header, $row );
                }
            }
        }
        fclose( $handle );
        return $data;
    }

    public function unzipAllFiles ( $dir, $strict = 0 ) {
        $zipFiles = glob( $dir . "*.zip" );
        $zip = new \ZipArchive;
        foreach ( $zipFiles as $zipFile ) {
            if ( $zip->open( $zipFile ) === TRUE ) {
                $name = $zip->getNameIndex(0);
                switch( $strict ) {
                    case 0:
                        $zip->extractTo( $dir );
                        $zip->close();
                        break;
                    case 1:
                        if( file_exists( $dir . $name ) || file_exists( $dir . $name . '.done' ) ) {
                            $this->log( $dir . $name. " File already extracted or processed. Looking for next one");
                        } else {
                            $zip->extractTo( $dir );
                            $zip->close();
                        }
                    break;
                }
            }
        }
    }

    public function arrayToCsv ( $data, $path ) {
        $file = fopen( $path, "w" );
        $i = 0;
        foreach( $data as $value ) {
            if( $i == 0 ) fputcsv( $file, array_keys( $value ) );
            fputcsv( $file, $value );
            $i++;
        }
    }

    public function allDates( $startDate, $endDate ) {
        $endDate = date('Y-m-d', strtotime( $endDate . ' +1 day' ) );
        $period = new \DatePeriod( new \DateTime( $startDate ), new \DateInterval('P1D'), new \DateTime( $endDate ) );
        $allDates = array();

        foreach ( $period as $dt ) {
            $allDates[] =  $dt->format( "Y-m-d" );
        }
        return $allDates;
    }

    public function weekStartDates( $startDate, $endDate ) {
        $period = new \DatePeriod( new \DateTime( $startDate ), new \DateInterval('P1W'), new \DateTime( $endDate ) );
        $weekStartDates = array();

        foreach ($period as $dt) {
            $weekStartDates[] =  $dt->format("Y-m-d");
        }

        return $weekStartDates;
    }

    public function sendEmails($subject, $text ) {
        $emailObj = new MailerController();
        $emailObj->sendEmail($subject, $text);
    }

    public function flattenArray( $arg ) {
        return is_array( $arg ) ? array_reduce( $arg, function ( $c, $a ) { return array_merge( $c, Tasks::flattenArray( $a ) ); } , [] ) : [$arg];
    }

    public function log( $str ) {
        echo "[" . date( 'Y-m-d H:i:s' ) . "] \t" . $str . "\n";
    }

    public function checkColumnPresentInCsv( $fileName, $columnName ) {
        if( !file_exists( $fileName ) || !is_readable( $fileName ) )
        die( $fileName . " f: checkColumnPresentInFile ** File not found or doesn't have sufficient permission while importing to csv.**");

        $delimiter = ',';

        if ( ( $handle = fopen( $fileName, 'r' ) ) !== FALSE) {
            while ( ( $row = fgetcsv( $handle, 100000, $delimiter ) ) !== FALSE ) {
                if( ( array_search( $columnName, $row ) ) ) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            }
        }
        fclose( $handle );
    }

    public function validateColumns(  $fileName, $columnName, $line = 1 ) {
        if( !file_exists( $fileName ) || !is_readable( $fileName ) )
        die( $fileName . " f:validateColumns ** File not found or doesn't have sufficient permission while importing to csv.**");

        if( $line < 1 ) {
            echo 'Notice: Line Number should be greater than 0 [zero]';
            $line = 1;
        }

        $line = $line - 1;
        $spl = new \SplFileObject($fileName);
        $spl->seek( $line );
        $line = str_replace('"', "", explode( ',', trim( $spl->current() ) ) );
        $columnName = explode(',', $columnName);

        if( $line == $columnName ){
            return true;
        } else {
            return false;
        }
    }

    public function validateDateCSV( $file, $dateColumn = 'Date', $format = 'Y-m-d H:i:s' ) {
        $data =  $this->importCsv($file);
        if( !$data[0] === FALSE ) {
            foreach ($data as $value) {
                $d = DateTime::createFromFormat( $format, $value[ $dateColumn ] );
                if( $d == TRUE  ){
                    if( $d->format( $format ) == $value[ $dateColumn ]  ){
                        $true[]= "TRUE";
                    } else {
                        $false[] = "FALSE";
                    }
                } else {
                   $false[] = "FALSE";
                }
            }
            if( !isset( $false ) ){
                return TRUE;
            } else {
                return FALSE;
            }

        } else {
            $this->log( $file . " is an Invalid CSV. ");
            return FALSE;
        }
    }

    public function returnYmd( $date ) {
        if( is_array( $date ) ) {
            foreach ( $date as $d ) {
                $d = date('Y-m-d', strtotime( $d ) ) ;
                $result[] = $d;
            }
            return $result;
        } else {
            $date = date('Y-m-d', strtotime( $date ) ) ;
            return $date;
        }
    }

    public function getQuarter( $querydate ) {
        return ceil( date( 'm', strtotime( $querydate ) ) / 3 );
    }

    public function getQuarterDates( $currentDate ) {
        $currentQuarter = $this->getQuarter( $currentDate );
        $datesplit = array('d' => date( 'd', strtotime( $currentDate ) ), 'm' => date( 'm', strtotime( $currentDate ) ), 'y' => date( 'Y', strtotime( $currentDate ) ) );
        for ( $i = 0; $i < 5; $i++) {
            $quarter = $currentQuarter - $i;
            if ( $quarter <= 0 ) {
                $quarter = $quarter + 4;
                if ( $quarter % 4 == 0 ) {
                    $datesplit['y'] = $datesplit['y'] - 1;
                }
            }
            $month = 1;
            for ( $j = 1; $j < $quarter ; $j++) {
                $month =  $month + 3;
            }
            $qStartDate = $datesplit['y'] . "-" . str_pad( $month, 2, "0", STR_PAD_LEFT) . "-01";
            $qEndDate = date( 'Y-m-d', strtotime( "+3 month -1 day", strtotime( $qStartDate ) ) );
            $qDates[$i] = array( 'startDate' => $qStartDate, 'endDate' => $qEndDate );
        }
        return $qDates;
    }
}
