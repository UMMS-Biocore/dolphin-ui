
stepCheck = 'stepCheck%(sep)s@RUNCHECK -i @INPUT -b @BARCODES -w @WKEY -p %(runparamsid)s -d @DBCOMMCMD -a @ADAPTER -t @TRIM -o @OUTDIR -r %(resume)s -s stepCheck -j @JGEN%(sep)s1'
stepSeqMapping = 'stepSeqMapping%(indexname)s%(sep)s@RUNRECMAPPING -i @INPUT -a @AWKDIR -d @SPAIRED -m @SAMTOOLS -o @OUTDIR -b @PARAM%(indexname)s -c @BOWTIE2CMD -s stepSeqMapping%(indexname)s -r @ADVPARAMS -j @JMAPPING%(sep)s1'
stepBarcode = 'stepBarcode%(sep)s@RUNBARCODE -i @INPUT -d @SPAIRED -b @BARCODES -o @OUTDIR -c @SINGLEBSPLITTERCMD:@PAIREDBSPLITTERCMD -s stepBarcode -j @JGEN%(sep)s1'
stepGetTotalReads = 'stepGetTotalReads%(sep)s@RUNGETTOTALREADS -b @BARCODES -r %(runparamsid)s -p @SPAIRED -o @OUTDIR -u @USERNAME -j @JGEN%(sep)s10'
stepBackupS3 = 'stepBackupS3%(sep)s@RUNBACKUPS3 -a %(amazonupload)s -b @BARCODES -r %(runparamsid)s -p @SPAIRED -o @OUTDIR -u @USERNAME -j @JGEN%(sep)s10'
stepFastQC = 'stepFastQC%(sep)s@PERL @RUNFASTQC -b @BARCODES -o @OUTDIR -p @FASTQCPROG -s stepFastQC -j @JGEN%(sep)s1'
stepMergeFastQC = 'stepMergeFastQC%(sep)s@PERL @RUNFASTQCMERGE -p @PUBDIR -w @WKEY -o @OUTDIR%(sep)s0'
stepAdapter = 'stepAdapters%(sep)s@RUNADAPTER -d @SPAIRED -p @PREVIOUSADAPTER -o @OUTDIR -a @ADAPTER -c @MATICCMD -s stepAdapter -j @JGEN%(sep)s1'
stepQuality = 'stepQuality%(sep)s@RUNQUALITY -d @SPAIRED -p @PREVIOUSQUALITY -o @OUTDIR -q @QUALITY -c @MATICCMD -s stepQuality -j @JGEN%(sep)s1'
stepTrim = 'stepTrim%(sep)s@RUNTRIM -t @TRIM -d @TRIMPAIRED -p @PREVIOUSTRIM -o @OUTDIR -c @TRIMMERCMD -s stepTrim -j @JGEN%(sep)s1'
stepRSEM = 'stepRSEM%(sep)s@RUNRSEM -c @RSEMCMD -r @RSEMREF -d @SPAIRED -pa @PARAMSRSEM -pr @PREVIOUSPIPE -o @OUTDIR -b @BOWTIEPATH -s stepRSEM -j @JRSEM%(sep)s10'
stepRSEMCount='stepRSEMCount%(g_i)s%(t_e)s%(sep)s@RUNRSEMCOUNT -p @PUBDIR -w @WKEY -o @OUTDIR -t %(t_e)s -g %(g_i)s -s stepRSEMCount%(g_i)s%(t_e)s -j @JGEN%(sep)s0'
stepTophat='stepTophat2%(sep)s@PERL @RUNTOPHAT2 -o @OUTDIR -g @GTF -d @SPAIRED -pa @PARAMSTOPHAT -pr @PREVIOUSPIPE -t @TOPHAT2CMD -b @BOWTIE2INDEX -sa @SAMTOOLS -se stepTophat2 -j @JTOPHAT%(sep)s5'
stepSplit='stepSplit%(sep)s@PERL @RUNSPLIT -o @OUTDIR -d @SPAIRED -p @PREVIOUSSPLIT -n %(thenumberofreads)s -se stepSplit -j @JGEN%(sep)s5'
stepMergeChip='stepMergeChip%(sep)s@PERL @RUNMERGECHIP -o @OUTDIR -d @SPAIRED -sa @SAMTOOLS -se stepMergeChip -j @JGEN%(sep)s5'
stepMACS='stepMACS%(sep)s@RUNMACS -a @MACSCMD -i @CHIPINPUT -o @OUTDIR -s stepMacs -p @PREVIOUSPIPE -j @JMACS%(sep)s10'
stepAggregation='stepAggregation%(sep)s@RUNAGGREGATION -o @OUTDIR -a @ACT -b @BTOOLSGENCOV -c @INTOPDF -p @PREVIOUSPIPE -g @GENOMESIZE -r @REFACT -s stepAggregation -j @JAGG%(sep)s10'
stepIGVTDF='stepIGVTDF%(type)s%(sep)s@RUNIGVTDF -o @OUTDIR -g @GENOMEFASTA -pa @SPAIRED -pu @PUBDIR -w @WKEY -l @TSIZE -sa @SAMTOOLS -t %(type)s -i @IGVTOOLS -se stepIGVTDF%(type)s -j @JGEN%(sep)s0'
stepBam2BW='stepBam2BW%(type)s%(sep)s@RUNBAM2BW -o @OUTDIR -g @GENOMESIZE -p @PUBDIR -wk @WKEY -c @RUNCOVERAGE -t %(type)s -wi @WIGTOBIGWIG -s stepBam2BW%(type)s -j @JGEN%(sep)s0'
stepPicard='stepPicard%(sep)s@PERL @RUNPICARD -o @OUTDIR -r @REFFLAT -p @PICARDCMD -s stepPicard -j @JGEN%(sep)s0'
stepCounts = 'stepCounts%(sep)s@RUNCOUNTS -m @MAPNAMES -o @OUTDIR -g @GCOMMONDB -p @PUBDIR -w @WKEY -b @MAKEBED -c @BEDTOOLSCMD -s stepCounts -j @JGEN%(sep)s1'
stepDESeq2 = 'stepDESeq2p%(deseq_count)s%(sep)s@RUNDESEQ2 -c @COLS%(deseq_count)s -pu @PUBDIR -w @WKEY -d @CONDS%(deseq_count)s -r @RSCRIPT -o @OUTDIR -n %(deseq_count)s -e @HEATMAP%(deseq_count)s -t @FITTYPE%(deseq_count)s -pa @PADJ%(deseq_count)s -f @FOLDCHANGE%(deseq_count)s -s stepDESeq2p%(deseq_count)s -j @JGEN%(sep)s0'
stepClean      = 'stepClean%(sep)s@RUNCLEAN -l %(level)s -p @PUBDIR -w @WKEY -d @DBCOMMCMD  -o @OUTDIR%(sep)s1'

