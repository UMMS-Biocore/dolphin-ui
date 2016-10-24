import re
import sys
import os
import GEOparse

accs = sys.argv[1]
out_dict = {}

for accession in accs.split(',', 1):
    geo = GEOparse.get_GEO(geo=accession, destdir="./")
    if (accession[:3].upper() == 'GSE'):
        for gsm_name, gsm in geo.gsms.iteritems():
            if (gsm_name not in out_dict):
                relations = {}
                sra_check = "false"
                if 'relation' in gsm.metadata:
                    for relation in gsm.metadata['relation']:
                        tmp = re.split(r':\s+', relation)
                        relname = tmp[0]
                        relval = tmp[1]
                        if relname in relations:
                            relations[relname].append(relval)
                        else:
                            relations[relname] = [relval]
                    if 'SRA' in relations:
                        sra_check = "true"
                out_dict[gsm_name] = sra_check
    elif (accession[:3].upper() == 'GSM'):
        if (geo.name not in out_dict):
            relations = {}
            sra_check = "false"
            if 'relation' in geo.metadata:
                for relation in geo.metadata['relation']:
                    tmp = re.split(r':\s+', relation)
                    relname = tmp[0]
                    relval = tmp[1]
                    if relname in relations:
                        relations[relname].append(relval)
                    else:
                        relations[relname] = [relval]
                if 'SRA' in relations:
                    sra_check = "true"
            out_dict[geo.name] = sra_check
    else:
        print 'Can only process GSM or GSE queries.'

print out_dict
