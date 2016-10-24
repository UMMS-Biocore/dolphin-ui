import re
import sys
import os
import GEOparse

accs = sys.argv[1]
out_dict = {}

class geotest():
    def relationGather(self, accession):
        geo = GEOparse.get_GEO(geo=accession, destdir="./")
        if (accession[:3].upper() == 'GSE'):
            for gsm_name, gsm in geo.gsms.iteritems():
                sra_end = "none"
                if (gsm_name not in out_dict):
                    relations = {}
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
                            sra_end = self.pairCheck(relations['SRA'][0])
                    out_dict[gsm_name] = sra_end
        elif (accession[:3].upper() == 'GSM'):
            if (geo.name not in out_dict):
                sra_end = "none"
                relations = {}
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
                        sra_end = self.pairCheck(relations['SRA'][0])
                out_dict[geo.name] = sra_end
        else:
            return 'Can only process GSM or GSE queries.'
        return "pass"
            
    def pairCheck(self, sra):
        child = os.popen('curl '+sra+' | grep "Layout: <span>PAIRED</span>"')
        response = child.read().rstrip()
        err = child.close()
        if response != "":
            sra_end = "paired"
            return sra_end
        else:
            child = os.popen('curl '+sra+' | grep "Layout: <span>SINGLE</span>"')
            response = child.read().rstrip()
            err = child.close()
            if response != "":
                sra_end = "single"
                return sra_end
        return "none"
    
def main():
    newgeotest = geotest();
    for accession in accs.split(',', 1):
        gather = newgeotest.relationGather(accession)
        if gather != "pass":
            print gather
            break
    print out_dict
    
if __name__ == "__main__":
    main()
